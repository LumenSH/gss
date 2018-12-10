<?php

namespace GSS\Controller\Frontend;

use GSS\Component\Content\Blog;
use GSS\Component\Content\Blog as BlogComponent;
use GSS\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Blog.
 */
class BlogController extends Controller
{
    /**
     * @var BlogComponent
     */
    private $blogComponent;

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    public function init()
    {
        $this->blogComponent = $this->container->get(Blog::class);
    }

    /**
     * @Route("/blog")
     * @Route("/blog/")
     *
     * @return Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function indexAction()
    {
        $this->data['currentPage'] = $this->Request()->query->get('page', 1);
        $this->data['blog'] = $this->blogComponent->getBlogPosts($this->data['currentPage'], 4, $this->userID);
        $this->data['maxPage'] = \ceil($this->data['blog']['total'] / 4);
        $this->data['breadcrumb'] = [
            [
                'name' => 'Blog',
            ],
        ];
        $this->data['record'] = [
            'meta' => [
                'keywords' => 'gs3, blogs',
                'description' => 'latest gs3 news',
            ],
        ];

        $this->data['pageTitel'] = 'Latest Gameserver-Sponsor news';
        $this->data['pageDescription'] = '';
        $this->data['pageImage'] = $this->container->getParameter('url') . 'src/img/logo_256x256.png';

        $this->View()->setPageTitle('Blog');

        return $this->render('frontend/blog/index.twig', $this->data);
    }

    /**
     * @Route("/blog/{name}")
     *
     * @return Response
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function detailAction($name = null)
    {
        $rewriteOptions = $this->container->get('rewrite_manager')->getRewriteParamsByUrl('blog/' . $name);

        if (isset($rewriteOptions['forwardParams'])) {
            $postID = \json_decode($rewriteOptions['forwardParams'], true)['postID'];
        } else {
            throw new NotFoundHttpException();
        }

        $comment = $this->Request()->getPost('comment');

        if (!empty($comment) && !empty(\trim($comment)) && !empty($this->userID) && \strlen(\trim($comment)) > 3) {
            $this->blogComponent->addComment($postID, $this->getUser(), $comment, $this->Request()->getPost('parent'));

            return $this->reload();
        } elseif (!empty($comment)) {
            $this->container->get('session')->flashMessenger()->addError('Blog', __('Dein Kommentar muss mindestens 3 Zeichen lang sein', 'Blog', 'CommentRequiredLength3'));
        }

        $this->data['blogItem'] = $this->blogComponent->getSingleBlogPost($postID, $this->userID);
        $this->data['recent'] = $this->blogComponent->getRecentArticles();

        if (!empty($this->data['blogItem']['tags'])) {
            $this->data['articleByTag'] = $this->blogComponent->getArticlesByTags(\explode(',', $this->data['blogItem']['tags']), $this->data['blogItem']);
        }

        if (empty($this->data['blogItem'])) {
            return $this->redirectToRoute('index');
        }
        $this->data['breadcrumb'] = [
                [
                    'name' => 'Blog',
                    'link' => $this->generateUrl('gss_frontend_blog_index'),
                ],
                [
                    'name' => $this->data['blogItem']['title'],
                ],
            ];

        $this->data['record'] = [
                'title' => $this->data['blogItem']['title'],
                'meta' => [
                    'keywords' => \str_replace(' ', ',', $this->data['blogItem']['title']),
                    'description' => $this->data['blogItem']['title'],
                ],
            ];

        $this->data['pageTitel'] = $this->data['blogItem']['title'];
        $this->data['pageDescription'] = \substr(\strip_tags($this->data['blogItem']['content']), 0, 100);
        $this->data['pageDescription'] = \preg_replace('/ [^ ]*$/', ' ...', $this->data['pageDescription']);
        $this->data['pageImage'] = $this->container->getParameter('url') . 'uploads/blog/' . $this->data['blogItem']['image'];

        return $this->render('frontend/blog/detail.twig', $this->data);
    }

    /**
     * @Route("/blog/deleteComment/{id}")
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function deleteCommentAction($id)
    {
        $row = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM blog_comments WHERE id = ?', [$id]);

        if ($row['user_id'] == $this->userID || $this->container->get('session')->Acl()->isAllowed('admin_blog_deletecomment')) {
            $this->blogComponent->deleteComment($id);
        }

        return new RedirectResponse($this->container->getParameter('url') . $this->container->get('rewrite_manager')->getRewriteByParams(['postID' => $row['blog_id']])['link']);
    }
}
