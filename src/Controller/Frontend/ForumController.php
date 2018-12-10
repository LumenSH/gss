<?php

namespace GSS\Controller\Frontend;

use GSS\Component\Community\Forum;
use GSS\Component\Community\Forum as ForumComponent;
use GSS\Component\Form\Forms\ForumPostEditType;
use GSS\Component\Form\Forms\ForumThreadType;
use GSS\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Forum Page.
 */
class ForumController extends Controller
{
    const MAXPERPAGE = 7;

    /** @var ForumComponent */
    private $forumComponent;

    public function init()
    {
        $this->forumComponent = $this->container->get(Forum::class);
        $this->data['breadcrumb'] = [
            [
                'name' => 'Forum',
                'link' => $this->generateUrl('gss_frontend_forum_index'),
            ],
        ];

        $this->data['isForumAdmin'] = $this->container->get('session')->Acl()->isAllowed('forum');
    }

    /**
     * @Route("/forum")
     * @Route("/forum/")
     *
     * @return string|\Symfony\Component\HttpFoundation\Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function indexAction()
    {
        $this->View()->setPageTitle('Forum');

        $this->data['lastPosts'] = $this->forumComponent->getLatestPosts();
        $this->data['boards'] = $this->forumComponent->getBoards();

        return $this->render('frontend/forum/index.twig', $this->data);
    }

    /**
     * @Route("/forum/create/{boardID}")
     * Thread Create Action.
     *
     * @param null $boardID
     *
     * @return string
     */
    public function createAction($boardID = null)
    {
        if (empty($boardID)) {
            return $this->redirectToRoute('gss_frontend_forum_index');
        }

        $this->View()->setPageTitle('Neues Thema erstellen');

        if ($this->userID === null) {
            $this->container->get('session')->flashMessenger()->addError('Forum', __('Um diese Funktion zu nutzen zukönnen benötigst du ein Account. Bitte registriere dich um Fortzufahren.', 'Forum', 'FeatureRequiredLogin'));

            return $this->redirectToRoute('gss_frontend_forum_index');
        }

        $forumThread = $this->container->get('form.factory')->create(ForumThreadType::class);
        $forumThread->handleRequest($this->Request());

        if ($forumThread->isSubmitted() && $forumThread->isValid()) {
            $this->forumComponent->createThread($boardID, $forumThread);
        }

        $this->data['breadcrumb'][] = [
            'name' => __('Neuen Beitrag anlegen', 'Forum', 'CreateNewThread'),
            'link' => '#',
        ];

        return $this->render('frontend/forum/create.twig', [
            'form' => $forumThread->createView(),
        ] + $this->data);
    }

    /**
     * @Route("/forum/edit/{entrieId}")
     * Entrie Edit Action.
     *
     * @param $entrieId
     *
     * @return string
     */
    public function editAction($entrieId)
    {
        $this->View()->setPageTitle('Beitrag bearbeiten');

        $this->data['entrie'] = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM forum_entries WHERE id = ?', [$entrieId]);

        if ($this->userID != $this->data['entrie']['userID'] && !$this->container->get('session')->Acl()->isAllowed('forum')) {
            return $this->redirectToRoute('gss_frontend_forum_index');
        }

        $forumPostForm = $this->container->get('form.factory')->create(ForumPostEditType::class);
        $forumPostForm->handleRequest($this->Request());

        if ($this->Request()->isGet()) {
            $forumPostForm->get('editor')->setData($this->data['entrie']['message']);
        }

        if ($forumPostForm->isSubmitted() && $forumPostForm->isValid()) {
            $this->forumComponent->editPost(
                $this->data['entrie']['threadID'],
                $entrieId,
                $this->Request()->xss_clean($forumPostForm->get('editor')->getData())
            );
        }

        return $this->render('frontend/forum/create.twig', [
            'form' => $forumPostForm->createView(),
        ] + $this->data);
    }

    /**
     * @Route("/forum/deleteEntrie/{id}")
     *
     * @param $id
     *
     * @return RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function deleteEntrieAction($id)
    {
        $this->data['entrie'] = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM forum_entries WHERE id = ?', [$id]);

        if ($this->userID != $this->data['entrie']['userID'] && !$this->container->get('session')->Acl()->isAllowed('forum')) {
            return $this->redirectToRoute('gss_frontend_forum_index');
        }

        $this->container->get('doctrine.dbal.default_connection')->executeQuery('DELETE FROM forum_entries WHERE id = ?', [
            $id,
        ]);

        if ($this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) FROM forum_entries WHERE threadID = ?', [
            $this->data['entrie']['threadID'],
        ]) == 0) {
            $this->forumComponent->deleteThread($this->data['entrie']['threadID']);
        }

        $this->container->get('cache')->delete('forumBoards');

        return new RedirectResponse($this->container->getParameter('url') . $this->container->get('rewrite_manager')->getRewriteByParams(['threadID' => $this->data['entrie']['threadID']])['link']);
    }

    /**
     * @Route("/forum/delete/{id}")
     *
     * @param $id
     *
     * @return RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function deleteAction($id)
    {
        if ($this->container->get('session')->Acl()->isAllowed('forum')) {
            $this->forumComponent->deleteThread($id);
        }

        return $this->redirectToRoute('gss_frontend_forum_index');
    }

    /**
     * @Route("/forum/close/{id}")
     *
     * @param $id
     *
     * @return RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function closeAction($id)
    {
        if ($this->container->get('session')->Acl()->isAllowed('forum')) {
            $this->forumComponent->closeThread($id);
        }

        return new RedirectResponse($this->container->getParameter('url') . $this->container->get('rewrite_manager')->getRewriteByParams(['threadID' => $id])['link']);
    }

    /**
     * @Route("/forum/like/{threadId}/{postId}")
     *
     * @param $threadId
     * @param $postId
     *
     * @return RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function likeAction($threadId, $postId)
    {
        if ($this->userID === null) {
            return $this->redirectToRoute('gss_frontend_forum_index');
        }

        $this->forumComponent->togglePostLike($postId);

        return new RedirectResponse($this->container->getParameter('url') . $this->container->get('rewrite_manager')->getRewriteByParams(['threadID' => $threadId])['link']);
    }

    /**
     * @Route("/forum/{uri}", requirements={"uri"=".+"})
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function forwardAction($uri)
    {
        $rewrite = $this->container->get('rewrite_manager')->getRewriteParamsByUrl('forum/' . $uri);

        if (isset($rewrite['forwardParams'])) {
            return \call_user_func_array([$this, $rewrite['forwardAction'] . 'Action'], \json_decode($rewrite['forwardParams'], true));
        }
        throw new NotFoundHttpException();
    }

    /**
     * Display Board.
     *
     * @param $boardID
     *
     * @return string
     */
    public function boardAction($boardID)
    {
        $this->data['board'] = $this->forumComponent->getBoardInfo($boardID);
        $this->data['pageTitle'] = $this->data['board']['boardName'];
        $this->data['threads'] = $this->forumComponent->getThreadsByBoard($boardID);

        $this->data['breadcrumb'][] = [
            'name' => $this->data['board']['board1Name'],
            'link' => '#',
        ];
        $this->data['breadcrumb'][] = [
            'name' => $this->data['board']['boardName'],
            'link' => '#',
        ];

        $this->data['record']['meta'] = [
            'description' => $this->data['board']['board1Name'] . ' ' . $this->data['board']['boardName'],
            'keywords' => $this->data['board']['board1Name'] . ',' . $this->data['board']['boardName'],
        ];

        return $this->render('frontend/forum/board.twig', $this->data);
    }

    /**
     * Display Thread Page.
     *
     * @param $threadId
     *
     * @return string
     */
    public function threadAction($threadId)
    {
        $this->data['thread'] = $this->forumComponent->getThread($threadId);
        $this->data['pageTitle'] = $this->data['thread']['threadName'];

        $editor = $this->Request()->getPostHtml('editor');
        if (!empty($editor)) {
            if ($this->userID !== null) {
                return $this->forumComponent->createThreadAnswer($this->data['thread'], $editor);
            }

            $this->container->get('session')->flashMessenger()->addError('Forum', __('Um diese Funktion zu nutzen zukönnen benötigst du ein Account. Bitte registriere dich um Fortzufahren.', 'Forum', 'FeatureRequiredLogin'));
        }

        $currentPage = $this->Request()->get('page', 1);
        $pages = \ceil($this->data['thread']['postCount'] / ForumComponent::MAXPERPAGE);
        $this->data['perPage'] = ForumComponent::MAXPERPAGE;
        $this->data['currentPage'] = $currentPage;
        $this->data['maxPage'] = $pages;
        $this->container->set('jsData', ['Mention' => ['items' => $this->forumComponent->getMentionsAvailable($threadId)]]);

        if ($currentPage > $pages) {
            throw new NotFoundHttpException();
        }

        $this->data['entries'] = $this->forumComponent->getThreadPosts($threadId, $currentPage);
        $this->data['breadcrumb'] = $this->forumComponent->getThreadBreadCrumb($threadId, $this->data);
        $this->data['bodyClass'] = 'act_thread';

        if (!empty($this->userID)) {
            $this->data['userEntrie'] = $this->getUserEntrie();
        }

        $this->data['record']['meta'] = [
            'description' => \strip_tags($this->data['entries'][0]['message']),
            'keywords' => \str_replace(' ', ',', $this->data['thread']['threadName']),
        ];

        return $this->render('frontend/forum/thread.twig', $this->data);
    }

    private function getUserEntrie()
    {
        return [
            'username' => $this->container->get('session')->getUserData('Username'),
            'userID' => $this->userID,
            'date' => \time(),
            'message' => '<form method="post"><textarea name="editor" data-ckeditor="true"></textarea> <button class="btn btn-primary pull-right mt20">' . __('Antworten', 'Forum', 'Answer') . '</button></form>',
            'userAvatar' => $this->container->get('session')->getUserData('Avatar'),
            'userRankPoints' => $this->container->get('session')->getUserData('RankPoints'),
            'userSignatur' => $this->container->get('session')->getUserData('Signatur'),
            'RegisterDate' => \date('Y-m-d'),
            'Role' => $this->container->get('session')->getUserData('Role'),
            'entriesCount' => $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) FROM forum_entries entries WHERE entries.userID = ?', [$this->userID]) + 1,
            'rank' => $this->container->get('twig')->getGlobals()['User']['Rank'],
            'userSlug' => '',
        ];
    }
}
