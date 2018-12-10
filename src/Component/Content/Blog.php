<?php

namespace GSS\Component\Content;

use Doctrine\DBAL\Connection;
use GSS\Component\Language\Language;
use GSS\Component\Routing\RewriteManager;
use GSS\Component\Security\User as UserModel;
use GSS\Component\User\User;
use GSS\Component\Util;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * Class Blog.
 */
class Blog
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $language;

    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * @var User
     */
    private $user;

    /**
     * @var RewriteManager
     */
    private $rewriteManager;

    /**
     * Blog constructor.
     *
     * @param Connection       $connection
     * @param Language         $language
     * @param AdapterInterface $cache
     * @param User             $user
     * @param RewriteManager   $rewriteManager
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function __construct(
        Connection $connection,
        Language $language,
        AdapterInterface $cache,
        User $user,
        RewriteManager $rewriteManager
    ) {
        $this->connection = $connection;
        $this->cache = $cache;
        $this->user = $user;
        $this->rewriteManager = $rewriteManager;

        $language->setLocale();
        $this->language = $language->getCountryCode();

        if ($this->language !== 'de' && $this->language !== 'en') {
            $this->language = 'en';
        }
    }

    /**
     * Get multiple Blog Posts.
     *
     * @param int $page
     * @param int $limit
     * @param int $userId
     *
     * @return array
     */
    public function getBlogPosts($page = 1, $limit = 3, $userId = 0)
    {
        if (empty($userId)) {
            $userId = 0;
        }

        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select('*, (SELECT Avatar FROM users WHERE ID = blog.user_id) as Avatar, (SELECT COUNT(*) FROM likes WHERE `table` = "blog" AND table_id = blog.id) as likes,(SELECT COUNT(*) FROM likes WHERE `table` = "blog" AND table_id = blog.id AND user_id = ' . $userId . ') as liked, (SELECT COUNT(*) FROM blog_comments WHERE blog_id = blog.id) as comments')
            ->from('blog')
            ->setMaxResults($limit)
            ->where('blog.publish = 1')
            ->orderBy('id', 'DESC')
            ->setFirstResult(Util::getSqlOffset($page, $limit));

        $data = $qb->execute()->fetchAll();

        /*
         * Set main variable to user language
         */
        foreach ($data as &$row) {
            $language = $this->getFallbackLanguageFromBlogPost($row);

            $row['title'] = $row['title_' . $language];
            $row['content'] = $row['content_' . $language];
            $row['month'] = \strftime('%B', $row['date']);
            $row['url'] = $this->rewriteManager->getRewriteByParams(['postID' => $row['id']])['link'];
        }

        return [
            'items' => $data,
            'total' => $this->connection->fetchColumn('SELECT COUNT(*) FROM blog WHERE publish = 1'),
        ];
    }

    /**
     * @param int $id
     * @param int $userId
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getSingleBlogPost(int $id, $userId = 0)
    {
        if (empty($userId)) {
            $userId = 0;
        }

        $sql = '
            SELECT
            *,
            (SELECT Avatar FROM users WHERE id = blog.user_id) as Avatar,
            (SELECT COUNT(*) FROM likes WHERE `table` = "blog" AND table_id = blog.id) as likes,
            (SELECT COUNT(*) FROM likes WHERE user_id = :userId AND`table` = "blog" AND table_id = blog.id) as liked
            
            FROM blog WHERE id = :blogId
        ';

        $blogItem = $this->connection->fetchAssoc($sql, [
            'userId' => $userId,
            'blogId' => $id,
        ]);

        if (empty($blogItem)) {
            return [];
        }

        $language = $this->getFallbackLanguageFromBlogPost($blogItem);

        $blogItem['title'] = $blogItem['title_' . $language];
        $blogItem['content'] = $blogItem['content_' . $language];
        $blogItem['month'] = \strftime('%B', $blogItem['date']);
        $blogItem['comments'] = $this->getComments($id, $userId);

        return $blogItem;
    }

    /**
     * @param int       $blog
     * @param UserModel $user
     * @param string    $comment
     * @param null      $parent
     *
     * @author Soner Sayakci <***REMOVED***>
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addComment(int $blog, UserModel $user, string $comment, $parent = null)
    {
        $this->connection->insert('blog_comments', [
            'blog_id' => $blog,
            'user_id' => $user->getId(),
            'comment' => $comment,
            'date' => \time(),
            'parent' => $parent,
        ]);

        $blogName = $this->connection->fetchColumn('SELECT title_de FROM blog WHERE id = ?', [$blog]);

        \preg_match_all('/@[a-zA-Z]+:/', $comment, $matches);

        foreach ($matches as $match) {
            if (!isset($match[0])) {
                continue;
            }

            $match = $match[0];
            $name = \substr(\substr($match, 1), 0, -1);

            if ($id = $this->connection->fetchColumn('SELECT id FROM users WHERE Username LIKE ?', [$name])) {
                if ($id != $user->getId()) {
                    $this->user->createNotification($id, $user->getId(), 'HighlightBlogComment', '<a href="%userLink%">%name%</a> hat dich im Blog Beitrag <a href="%url%">%blog%</a> erwähnt', [
                        'name' => $user->getUsername(),
                        'blog' => $blogName,
                        'url' => '/' . $this->rewriteManager->getRewriteByParams(['postID' => $blog])['link'],
                        'userLink' => '/' . $this->rewriteManager->getRewriteByParams(['userID' => $user->getId()])['link'],
                    ]);
                }
            }
        }

        $this->connection->executeQuery('UPDATE users SET RankPoints = RankPoints + 1 WHERE id = ?', [$user->getId()]);
    }

    public function deleteComment($id)
    {
        $this->connection->executeQuery('DELETE FROM blog_comments WHERE id = ? OR parent = ?', [$id, $id]);
    }

    public function getComments($id, $userId = 0)
    {
        $data = $this->connection->fetchAll('
            SELECT
              comments.*,
              users.Avatar,
              users.Username,
              (SELECT COUNT(*) FROM likes WHERE `table` = "comment" AND table_id = comments.id) AS `likes`,
              (SELECT COUNT(*) FROM likes WHERE user_id = ? AND `table` = "comment" AND table_id = comments.id) AS `liked`

            FROM
              blog_comments comments
            LEFT JOIN users
              ON(users.id = comments.user_id)
            WHERE
              comments.blog_id = ? AND
              comments.parent IS NULL
            ORDER BY (SELECT COUNT(*) FROM blog_comments WHERE blog_comments.parent = comments.blog_id),comments.date DESC
            ', [
            $userId,
            $id,
        ]);

        foreach ($data as &$comment) {
            $comment['subs'] = $this->connection->fetchAll('
            SELECT
              comments.*,
              users.Avatar,
              users.Username,
              (SELECT COUNT(*) FROM likes WHERE `table` = "comment" AND table_id = comments.id) AS `likes`,
              (SELECT COUNT(*) FROM likes WHERE user_id = ? AND `table` = "comment" AND table_id = comments.id) AS `liked`
            FROM
              blog_comments comments
            LEFT JOIN users
              ON(users.id = comments.user_id)
            WHERE
              comments.parent = ?
            ORDER BY (SELECT COUNT(*) FROM blog_comments WHERE blog_comments.parent = comments.blog_id),comments.date DESC', [
                $userId,
                $comment['id'],
            ]);
        }

        return $data;
    }

    public function getRecentArticles()
    {
        $cacheKey = 'recent_article_blog_' . $this->language;
        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            $data = $this->connection->fetchAll('SELECT id, date, title_de, title_de FROM blog ORDER BY id DESC LIMIT 5');

            foreach ($data as &$news) {
                $language = $this->getFallbackLanguageFromBlogPost($news);
                $news['title'] = $news['title_' . $language];
                $news['month'] = \strftime('%B', $news['date']);
                $news['url'] = $this->rewriteManager->getRewriteByParams(['postID' => $news['id']])['link'];
            }
            unset($news);

            $cacheItem->set($data);
            $this->cache->save($cacheItem);

            return $data;
        }

        return $cacheItem->get();
    }

    public function getArticlesByTags($tags, $viewArticle)
    {
        $cacheKey = 'recent_article_tag_blog_' . $this->language . $viewArticle['tags'];
        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            $sql = 'SELECT id, date, title_de, title_en FROM blog WHERE id != ' . $viewArticle['id'] . ' AND ';

            foreach ($tags as $tag) {
                $sql .= ' tags LIKE "%' . $tag . '%" OR';
            }

            $sql = \substr($sql, 0, -3);
            $data = $this->connection->fetchAll($sql);

            foreach ($data as &$blogItem) {
                $language = $this->getFallbackLanguageFromBlogPost($blogItem);
                $blogItem['title'] = $blogItem['title_' . $language];
                $blogItem['month'] = \strftime('%B', $blogItem['date']);
                $blogItem['url'] = $this->rewriteManager->getRewriteByParams(['postID' => $blogItem['id']])['link'];
            }
            unset($blogItem);

            $cacheItem->set($data);
            $cacheItem->expiresAfter(3600);
            $this->cache->save($cacheItem);

            return $data;
        }

        return $cacheItem->get();
    }

    /**
     * @param array $blogPost
     *
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getFallbackLanguageFromBlogPost(array $blogPost)
    {
        if (!isset($blogPost['title_' . $this->language])) {
            if ($this->language === 'de') {
                return 'en';
            }

            return 'de';
        }

        return $this->language;
    }
}
