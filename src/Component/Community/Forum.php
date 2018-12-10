<?php

namespace GSS\Component\Community;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Connection;
use GSS\Component\HttpKernel\Request;
use GSS\Component\Routing\RewriteManager;
use GSS\Component\Session\Session;
use GSS\Component\User\User;
use GSS\Component\Util;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class Forum.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class Forum
{
    const MAXPERPAGE = 7;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * @var RewriteManager
     */
    private $rewriteManager;

    /**
     * @var Slugify
     */
    private $slugify;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var User
     */
    private $user;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Request
     */
    private $request;

    /**
     * Forum constructor.
     *
     * @param Connection       $connection
     * @param AdapterInterface $cache
     * @param Request          $request
     * @param RewriteManager   $rewriteManager
     * @param Slugify          $slugify
     * @param Session          $session
     * @param User             $user
     * @param Util             $util
     * @param RouterInterface  $router
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function __construct(
        Connection $connection,
        AdapterInterface $cache,
        Request $request,
        RewriteManager $rewriteManager,
        Slugify $slugify,
        Session $session,
        User $user,
        RouterInterface $router
    ) {
        $this->connection = $connection;
        $this->cache = $cache;
        $this->request = $request;
        $this->rewriteManager = $rewriteManager;
        $this->slugify = $slugify;
        $this->session = $session;
        $this->user = $user;
        $this->router = $router;
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getLatestPosts()
    {
        $posts = $this->connection->fetchAll('
            SELECT
            i1.*,
            forum_thread.threadName,
            forum_thread.threadViews,
            (SELECT COUNT(*) FROM forum_entries WHERE forum_entries.threadID = i1.threadID) AS threadAnswers,
            (SELECT boardName FROM forum_board WHERE id = i1.boardID) AS boardName,
            (SELECT Username FROM users WHERE id = i1.userID) AS posterName,
            (SELECT Avatar FROM users WHERE id = i1.userID) AS posterAvatar,
            (SELECT Username FROM users WHERE id = (SELECT userID FROM forum_entries WHERE forum_entries.threadID = i1.threadID ORDER BY id ASC LIMIT 1)) AS creatorName
            FROM forum_entries AS i1
            LEFT JOIN forum_entries AS i2 ON (i1.threadID = i2.threadID AND i1.id < i2.id)
            LEFT JOIN forum_thread ON(forum_thread.id = i1.threadID)
            WHERE i2.id IS NULL
            ORDER BY id DESC
            LIMIT 5
        ');

        foreach ($posts as &$post) {
            $post['boardLink'] = $this->rewriteManager->getRewriteByParams(['boardID' => $post['boardID']])['link'];
            $post['threadLink'] = $this->rewriteManager->getRewriteByParams(['threadID' => $post['threadID']])['link'];
        }

        return $posts;
    }

    /**
     * @param int           $boardID
     * @param FormInterface $forumThread
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function createThread(int $boardID, FormInterface $forumThread)
    {
        $post = $forumThread->getData();
        $link = $this->rewriteManager->getRewriteByParams(['boardID' => $boardID])['link'];
        $baseThreadSlug = $link . '/' . $this->slugify->slugify($post['name']);

        $this->connection->insert('forum_thread', [
            'boardID' => $boardID,
            'threadName' => $post['name'],
            'threadDate' => \time(),
            'threadViews' => 0,
        ]);

        $insertId = $this->connection->lastInsertId();

        $threadSlug = $this->rewriteManager->addRewrite($baseThreadSlug, 'forum', 'thread', ['threadID' => $insertId]);

        $this->connection->insert('forum_entries', [
            'boardID' => $boardID,
            'threadID' => $insertId,
            'userID' => $this->session->getUserID(),
            'date' => \time(),
            'message' => $this->request->xss_clean($post['editor']),
        ]);

        $this->cache->deleteItem('forumBoards');

        $this->connection->executeQuery('UPDATE users SET RankPoints = RankPoints + 1 WHERE id = ?', [$this->session->getUserID()]);

        $this->session->flashMessenger()->addSuccess('Forum', __('Dein Thread wurde erfolgreich erstellt', 'Forum', 'ThreadCreated'));
        \header('Location: /' . $threadSlug);
        die();
    }

    /**
     * @param int $boardId
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getBoardInfo(int $boardId)
    {
        return $this->connection->fetchAssoc(
            'SELECT
            *,
            (SELECT boardName FROM forum_board WHERE id = board.boardSub) as board1Name
            FROM
            forum_board board
            WHERE id = ?',
            [$boardId]
        );
    }

    /**
     * @param int $boardId
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getThreadsByBoard($boardId)
    {
        $threads = $this->connection->fetchAll('
        SELECT
            forum_thread.*,
            (SELECT COUNT(*) FROM forum_entries WHERE forum_entries.threadID = forum_thread.id) AS entriesCount,
            (SELECT Username FROM users WHERE id = (SELECT userID FROM forum_entries WHERE threadID = forum_thread.id  ORDER BY id ASC LIMIT 1)) AS creatorUser,
            (SELECT Avatar FROM users WHERE id = (SELECT userID FROM forum_entries WHERE threadID = forum_thread.id  ORDER BY id ASC LIMIT 1)) AS creatorAvatar
        FROM forum_thread
        WHERE forum_thread.boardID = ?
        ORDER BY forum_thread.id DESC
        ', [
            $boardId,
        ]);

        foreach ($threads as &$thread) {
            $thread['link'] = $this->rewriteManager->getRewriteByParams(['threadID' => $thread['id']])['link'];
            $thread['lastAnswer'] = $this->connection->fetchAssoc('
            SELECT
            (SELECT Username FROM users WHERE id = forum_entries.userID) AS username,
            (SELECT Avatar FROM users WHERE id = forum_entries.userID) AS avatar,
                `date` FROM forum_entries
                WHERE threadID = ?
                ORDER BY id DESC
            ', [$thread['id']]);
        }

        return $threads;
    }

    /**
     * @param int $threadId
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getThread($threadId)
    {
        $this->connection->executeQuery('UPDATE forum_thread SET threadViews = threadViews + 1 WHERE id = ?', [$threadId]);

        $thread = $this->connection->fetchAssoc('
            SELECT
            forum_thread.*,
            (SELECT boardName FROM forum_board WHERE id = (SELECT boardSub FROM forum_board WHERE id = forum_thread.boardID)) as board1Name,
            (SELECT boardSub FROM forum_board WHERE id = forum_thread.boardID) as board1ID,
            (SELECT boardName FROM forum_board WHERE id = forum_thread.boardID) as board2Name,
            forum_thread.boardID as board2ID,
            firstEntry.userID as userID,
            (SELECT COUNT(*) FROM forum_entries WHERE threadID = forum_thread.id) as postCount,
            users.Username as creatorName,
            users.Avatar as creatorAvatar,
            firstEntry.date as creatorDate
            FROM forum_thread
            INNER JOIN (SELECT userID, date FROM forum_entries WHERE threadID = :forumThread ORDER BY id ASC LIMIT 1) as firstEntry
            INNER JOIN users ON(users.id = firstEntry.userID)
            WHERE forum_thread.id = :forumThread
        ', [':forumThread' => $threadId]);

        $thread['creatorLink'] = $this->rewriteManager->getRewriteByParams(['userID' => $thread['userID']])['link'];
        $thread['board2Link'] = $this->rewriteManager->getRewriteByParams(['boardID' => $thread['board2ID']])['link'];

        return $thread;
    }

    /**
     * @param array  $thread
     * @param string $editor
     *
     * @author Soner Sayakci <***REMOVED***>
     *
     * @return RedirectResponse
     */
    public function createThreadAnswer(array $thread, string $editor)
    {
        $this->connection->insert('forum_entries', [
            'boardID' => $thread['boardID'],
            'threadID' => $thread['id'],
            'userID' => $this->session->getUserID(),
            'date' => \time(),
            'message' => $editor,
        ]);

        $nextPage = \ceil(($thread['postCount'] + 1) / self::MAXPERPAGE);
        $url = $this->rewriteManager->getRewriteByParams(['threadID' => $thread['id']])['link'] . '?page=' . $nextPage;

        $this->connection->executeQuery('UPDATE users SET `RankPoints` = `RankPoints` + 1 WHERE `id` = ?', [$this->session->getUserID()]);

        if ($thread['userID'] != $this->session->getUserID()) {
            $this->user->createNotification($thread['userID'], $this->session->getUserID(), 'OnPostAnswer', '<a href="%userLink%">%name%</a> hat auf dein Thema %thread% geantwortet', [
                'name' => $this->session->getUserData('Username'),
                'thread' => '<a href="/' . $url . '">' . $thread['threadName'] . '</a>',
                'userLink' => '/' . $this->session->get('userSlug'),
            ]);
        }

        $this->cache->deleteItem('forumBoards');

        $this->session->flashMessenger()->addSuccess('Forum', __('Deine Antwort wurde erfolgreich gepostet', 'Forum', 'AnswerPosted'));

        return new RedirectResponse('/' . $url);
    }

    /**
     * @param int   $threadId
     * @param array $data
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getThreadBreadCrumb(int $threadId, array $data): array
    {
        $breadCrumbItem = $this->cache->getItem('thread_breadcrumb_' . $threadId);
        if (!$breadCrumbItem->isHit()) {
            $breadCrumb = [
                [
                    'name' => 'Forum',
                    'link' => $this->router->generate('gss_frontend_forum_index'),
                ],
                [
                    'name' => $data['thread']['board1Name'],
                    'link' => '/' . $this->rewriteManager->getLink('forum', 'board', [
                            'boardID' => $data['thread']['board1ID'],
                        ]),
                ],
                [
                    'name' => $data['thread']['board2Name'],
                    'link' => '/' . $this->rewriteManager->getLink('forum', 'board', [
                            'boardID' => $data['thread']['board2ID'],
                        ]),
                ],
                [
                    'name' => $data['thread']['threadName'],
                ],
            ];

            $breadCrumbItem->set($breadCrumb);
            $breadCrumbItem->expiresAfter(3600);
            $this->cache->save($breadCrumbItem);

            return $breadCrumb;
        }

        return $breadCrumbItem->get();
    }

    /**
     * @param int $threadId
     * @param int $page
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getThreadPosts(int $threadId, $page = 1)
    {
        $offset = Util::getSqlOffset($page, self::MAXPERPAGE);

        $posts = $this->connection->fetchAll('
            SELECT
                forum_entries.*,
                users.Username as username,
                users.Avatar as userAvatar,
                users.RankPoints as userRankPoints,
                users.Signatur as userSignatur,
                users.RegisterDate,
                users.Role,
                (SELECT COUNT(*) FROM forum_entries entries WHERE entries.userID = forum_entries.userID) AS entriesCount,
                (SELECT COUNT(*) FROM likes WHERE likes.`table` = "forum" AND table_id = forum_entries.id) AS likeCount
            FROM forum_entries
            LEFT JOIN users ON (users.id = forum_entries.userID)
            WHERE forum_entries.threadID = ?
            LIMIT ' . $offset . ', ' . self::MAXPERPAGE . '
        ', [$threadId]);

        foreach ($posts as &$entry) {
            $entry['rank'] = $this->user->getUserRank($entry['userRankPoints']);
            $entry['userSlug'] = $this->rewriteManager->getRewriteByParams([
                'userID' => $entry['userID'],
            ])['link'];
            $entry['likes'] = $this->getPostLikes($entry['id']);
        }

        return $posts;
    }

    /**
     * @param int    $threadId
     * @param int    $postId
     * @param string $message
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function editPost(int $threadId, int $postId, string $message)
    {
        $this->connection->update('forum_entries', [
            'message' => $message,
        ], ['id' => $postId]);

        $this->cache->deleteItem('forumBoards');

        \header('Location: /' . $this->rewriteManager->getRewriteByParams(['threadID' => $threadId])['link']);
        die();
    }

    /**
     * @param int $threadId
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function deleteThread($threadId)
    {
        $this->rewriteManager->removeRewriteByParams([
            'threadID' => $threadId,
        ]);
        $this->connection->delete('forum_thread', ['id' => $threadId]);

        $this->cache->deleteItem('forumBoards');
    }

    /**
     * @param int $threadId
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function closeThread($threadId)
    {
        $this->connection->executeQuery('UPDATE forum_thread SET threadClosed = 1 WHERE id = ?', [$threadId]);
    }

    /**
     * @param int $threadId
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getMentionsAvailable(int $threadId)
    {
        $mentions = $this->connection->fetchAll('SELECT userID as id, (SELECT Username FROM users WHERE users.id = forum_entries.userID) as name FROM forum_entries WHERE threadID = ? GROUP BY forum_entries.userID', [$threadId]);

        foreach ($mentions as &$mention) {
            $mention['url'] = '/' . $this->rewriteManager->getRewriteByParams(['userID' => $mention['id']])['link'];
        }

        return $mentions;
    }

    /**
     * @param int $postId
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function togglePostLike(int $postId)
    {
        $hasLiked = $this->connection->fetchColumn('SELECT 1 FROM likes WHERE `table` = "forum" AND table_id = ? AND user_id = ?', [
            $postId,
            $this->session->getUserID(),
        ]);

        if ($hasLiked) {
            $this->connection->delete('likes', ['user_id' => $this->session->getUserID(), '`table`' => 'forum', 'table_id' => $postId]);
        } else {
            $likedUser = $this->connection->fetchColumn('SELECT userID FROM forum_entries WHERE id = ?', [$postId]);
            if (!empty($likedUser)) {
                $this->connection->insert('likes', ['user_id' => $this->session->getUserID(), '`table`' => 'forum', 'table_id' => $postId, 'liked_user' => $likedUser]);
            }
        }
    }

    /**
     * Get the boards for Forum.
     *
     * @return array
     */
    public function getBoards()
    {
        $boardsItem = $this->cache->getItem('forumBoards');

        if (!$boardsItem->isHit()) {
            $tmpBoards = $this->connection->fetchAll('
            SELECT
              *,
              (SELECT forum_entries.date FROM forum_entries WHERE boardID = forum_board.id ORDER BY id DESC LIMIT 1) AS latestDate,
              (SELECT forum_thread.threadName FROM forum_thread WHERE forum_thread.id = (SELECT forum_entries.threadID FROM forum_entries WHERE boardID= forum_board.id ORDER BY id DESC LIMIT 1)) AS latestThreadName,
              (SELECT users.Username FROM users WHERE id = (SELECT forum_entries.userID FROM forum_entries WHERE boardID= forum_board.ID ORDER BY id DESC LIMIT 1)) AS latestUser,
              (SELECT users.Avatar FROM users WHERE id = (SELECT forum_entries.userID FROM forum_entries WHERE boardID= forum_board.ID ORDER BY id DESC LIMIT 1)) AS latestUserAvatar,
              (SELECT forum_entries.threadID FROM forum_entries WHERE boardID= forum_board.id ORDER BY id DESC LIMIT 1) AS latestLink
            FROM forum_board
        ');

            foreach ($tmpBoards as &$board2) {
                $board2['entries'] = $this->connection->fetchColumn('SELECT COUNT(*) FROM forum_entries WHERE boardID = ?', [$board2['id']]);
                $board2['threadCount'] = $this->connection->fetchColumn('SELECT COUNT(*) FROM forum_thread WHERE boardID = ?', [$board2['id']]);
                $board2['link'] = $this->rewriteManager->getRewriteByParams(['boardID' => $board2['id']])['link'];
                if (!empty($board2['latestLink'])) {
                    $board2['latestLink'] = $this->rewriteManager->getRewriteByParams(['threadID' => $board2['latestLink']])['link'];
                }
            }
            $newArray = [];

            foreach ($tmpBoards as $board) {
                $newArray[$board['id']] = $board;
            }
            $tmpBoards = $newArray;
            unset($newArray);

            $boards = [];

            foreach ($tmpBoards as $tmpBoard) {
                if (empty($tmpBoard['boardSub'])) {
                    $tmpBoard['subs'] = $this->getBoard($tmpBoards, $tmpBoard['id']);
                    $boards[] = $tmpBoard;
                }
            }

            $boardsItem->set($boards);
            $this->cache->save($boardsItem);

            return $boards;
        }

        return $boardsItem->get();
    }

    private function getPostLikes($postId)
    {
        $likes = $this->connection->fetchAll('SELECT likes.*, users.Username FROM likes LEFT JOIN users ON(users.id = likes.user_id) WHERE `table` = "forum" AND table_id = ?', [$postId]);

        foreach ($likes as &$like) {
            $like['Link'] = $this->rewriteManager->getRewriteByParams(['userID' => $like['user_id']])['link'];
        }

        return $likes;
    }

    /**
     * Recursive Function to get sub Boards.
     *
     * @param $boards
     * @param $boardParent
     *
     * @return array
     */
    private function getBoard($boards, $boardParent)
    {
        $returnArray = [];

        foreach ($boards as $board) {
            if ($board['boardSub'] == $boardParent) {
                $board['subs'] = $this->getBoard($boards, $board['id']);
                $returnArray[] = $board;
            }
        }

        return $returnArray;
    }
}
