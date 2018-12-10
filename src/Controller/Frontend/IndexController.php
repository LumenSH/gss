<?php

namespace GSS\Controller\Frontend;

use GSS\Component\Commerce\GP;
use GSS\Component\Content\Blog;
use GSS\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Index.
 */
class IndexController extends Controller
{
    protected $data = [];

    /**
     * @Route("/", name="index")
     * @Route("/index/", name="index_")
     */
    public function indexAction()
    {
        $this->View()->setPageTitle('Dashboard');

        $gp = $this->container->get('cache')->get('gpcount');
        $indexServer = $this->container->get('cache')->get('indexServer');
        $indexStats = $this->container->get('cache')->get('indexStats');

        if (empty($gp)) {
            $gp = $this->container->get(GP::class)->getGPCount();
            $this->container->get('cache')->set('gpcount', $gp, 3600);
        }

        $this->data['GP'] = $gp;
        $this->data['serverUsage'] = $indexServer;
        $this->data['stats'] = $indexStats;
        $this->data['blog'] = $this->container->get(Blog::class)->getBlogPosts(1, 3, $this->userID);
        $this->data['breadcrumb'] = [
            [
                'name' => 'Startseite',
                'link' => '#',
            ],
        ];

        $this->data['pageTitel'] = 'Gameserver-Sponsor.me';
        $this->data['pageDescription'] = 'Our gameserver provider for free servers';
        $this->data['pageImage'] = $this->container->getParameter('url') . 'src/img/logo_256x256.png';

        return $this->render('frontend/index/index.twig', $this->data);
    }

    /**
     * @Route("/index/changeLanguage/{code}")
     */
    public function changeLanguageAction($code = 'en')
    {
        $mapping = $this->container->getParameter('language')['mapping'];

        if (!\in_array($code, $mapping)) {
            $code = $this->container->getParameter('language')['defaultLanguage'];
        }

        if (!empty($this->userID)) {
            $this->container->get('doctrine.dbal.default_connection')->executeQuery('UPDATE users SET Language = ? WHERE id = ?', [$code, $this->userID]);
        }

        $this->container->get('language')->setLanguage($code);

        if ($this->Request()->query->has('redirect')) {
            return new RedirectResponse($this->Request()->query->get('redirect'));
        }

        return $this->redirectToRoute('index');
    }

    /**
     * Like Action.
     *
     * @Route("/index/like")
     */
    public function likeAction()
    {
        $id = $this->Request()->getPost('id');
        $section = $this->Request()->getPost('section');
        $like = $this->Request()->getPost('like');
        $userId = $this->container->get('session')->getUserID();

        /*
         * Block likeActions, who hasent filled all details
         */
        if (!isset($id, $like) || empty($userId) || empty($section)) {
            return;
        }

        /*
         * Getting Liked User ID
         */
        $likedUserId = null;
        switch ($section) {
            case 'comment':
                $likedUserId = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT user_id FROM blog_comments WHERE id = ?', [
                    $id,
                ]);
                break;

            case 'blog':
                $likedUserId = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT user_id FROM blog WHERE id = ?', [
                    $id,
                ]);
                break;
        }

        if ($like) {
            if (!$this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT 1 FROM likes WHERE user_id = ? AND `table` = ? AND table_id = ? AND liked_user = ?', [
                $userId,
                $section,
                $id,
                $likedUserId,
            ])) {
                $this->container->get('doctrine.dbal.default_connection')->executeQuery('INSERT INTO likes (user_id, `table`, table_id, liked_user) VALUES(?, ?, ?, ?)', [
                    $userId,
                    $section,
                    $id,
                    $likedUserId,
                ]);

                $this->container->get('doctrine.dbal.default_connection')->executeQuery('UPDATE users SET RankPoints = RankPoints + 1 WHERE id = ?', [$userId]);
            }
        } else {
            $this->container->get('doctrine.dbal.default_connection')->executeQuery('DELETE FROM likes WHERE user_id = ? AND `table` = ? AND `table_id` = ?', [
                $userId,
                $section,
                $id,
            ]);

            $this->container->get('doctrine.dbal.default_connection')->executeQuery('UPDATE users SET RankPoints = RankPoints - 1 WHERE ID = ?', [$userId]);
        }

        return new JsonResponse($this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) FROM likes WHERE `table` = ? AND `table_id` = ?', [
            $section,
            $id,
        ]));
    }

    /**
     * @Route("/index/partner/{userId}")
     * GP Einladungen.
     *
     * @param int $userId
     *
     * @return RedirectResponse
     */
    public function partnerAction($userId = 0)
    {
        if (!empty($userId)) {
            if ($this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) FROM users WHERE id = ?', [$userId]) == 1) {
                $ip = $this->Request()->getClientIp();
                if ($this->container->get('doctrine.dbal.default_connection')->fetchColumn(
                    'SELECT id FROM blocked_tasks WHERE Method = ? AND `Email` = ?',
                    [
                    'GPInvite', $ip,
                ]
                ) == 0) {
                    $this->container->get('doctrine.dbal.default_connection')->insert('blocked_tasks', [
                        'Method' => 'GPInvite',
                        'Email' => $ip,
                        'Value' => $userId,
                        'TTL' => \strtotime('+1days'),
                    ]);

                    $this->container->get(GP::class)->addPointsToUser($userId, $this->container->getParameter('gppoints.invite'), 'GS Banner');
                    $this->container->get('session')->set('Partner', $userId);
                }
            }
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/index/gameserver/{gameserverid}")
     * Action for Banner Reflink.
     *
     * @param int $gameserverid
     *
     * @return RedirectResponse
     */
    public function gameserverAction($gameserverid = 0)
    {
        $ip = $this->Request()->getClientIp();

        if (!empty($gameserverid)) {
            $gameserver = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM gameserver WHERE id = ?', [
                $gameserverid,
            ]);

            if (!empty($gameserver)) {
                if ($gameserver['userID'] != $this->container->get('session')->getUserID()) {
                    if ($this->container->get('doctrine.dbal.default_connection')->fetchColumn(
                        'SELECT COUNT(*) FROM blocked_tasks WHERE Method = ? AND `Email` = ?',
                        [
                        'GPServerBanner',
                        $ip,
                    ]
                    ) == 0) {
                        $this->container->get('doctrine.dbal.default_connection')->insert('blocked_tasks', [
                            'Method' => 'GPServerBanner',
                            'Email' => $ip,
                            'Value' => $gameserverid,
                            'TTL' => \strtotime('+1days'),
                        ]);

                        $this->container->get(GP::class)->addPointsToUser($gameserver['userID'], $this->container->getParameter('gppoints.serverbanner'), 'GS Server Banner');
                    }
                }
            }
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/index/markAllAsRead")
     *
     * @return RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function markAllAsReadAction()
    {
        if (!empty($this->userID)) {
            $this->container->get('doctrine.dbal.default_connection')->executeQuery('UPDATE users_notification SET `read` = 1 WHERE userID = ?', [$this->userID]);
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/index/tutorial")
     *
     * @return RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function tutorialAction()
    {
        if (!empty($this->userID)) {
            if ($this->container->get('session')->get('user/Intro') == 0) {
                $this->container->get('doctrine.dbal.default_connection')->update('users', ['Intro' => 1], ['id' => $this->userID]);
                $this->container->get(GP::class)->addPointsToUser(
                    $this->userID,
                    $this->container->getParameter('gppoints.tutorial'),
                    'Tutorial'
                );
            }
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/sitemap.xml")
     *
     * @return string|\Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function sitemapAction()
    {
        $links = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT link FROM core_rewrite');

        $view = $this->render('frontend/index/sitemap.twig', [
            'links' => $links,
        ]);
        $view->headers->set('Content-Type', 'application/xml');

        return $view;
    }
}
