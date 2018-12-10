<?php

namespace GSS\Controller\Backend;

use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class News.
 */
class BlogController extends Backend
{
    /**
     * @Route("/backend/blog")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * @Route("/backend/blog/tags")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tagsAction()
    {
        return new JsonResponse(['data' => $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM tags')]);
    }

    /**
     * @Route("/backend/blog/saveImage")
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveImageAction()
    {
        if (!\file_exists($this->container->getParameter('kernel.public_dir') . '/uploads/blog/')) {
            \mkdir($this->container->getParameter('kernel.public_dir') . '/uploads/blog/', 0777, true);
        }

        $blogId = $this->Request()->getPost('id');

        $img = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT image FROM blog WHERE id = ?', [$blogId]);

        if (!empty($img)) {
            if (\file_exists($this->container->getParameter('kernel.public_dir') . '/uploads/blog/' . $img)) {
                @\unlink($this->container->getParameter('kernel.public_dir') . '/uploads/blog/' . $img);
            }
        }

        if (!empty($_FILES['img']['name'])) {
            $extension = \pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
            $fileName = \uniqid() . '.' . $extension;

            \move_uploaded_file($_FILES['img']['tmp_name'], $this->container->getParameter('kernel.public_dir') . '/uploads/blog/' . $fileName);

            $this->container->get('doctrine.dbal.default_connection')->update('blog', ['image' => $fileName], ['id' => $blogId]);
        }

        return new JsonResponse();
    }

    /**
     * @param int    $limit
     * @param int    $page
     * @param string $sorting
     * @param array  $filter
     * @param array  $search
     *
     * @return array
     */
    protected function getList($limit = 25, $page = 1, $sorting = '', $filter = [], $search = [])
    {
        $data = $this->listQuery('blog', $limit, $page, $sorting, $filter, $search);

        foreach ($data['data'] as &$item) {
            $tags = \explode(',', $item['tags']);
            $item['tags'] = [];

            foreach ($tags as $tag) {
                if (!empty($tag)) {
                    $item['tags'][] = ['text' => $tag];
                }
            }
        }

        return $data;
    }

    protected function save($id, $data)
    {
        if (!empty($data['tags'])) {
            $tags = '';

            foreach ($data['tags'] as $tag) {
                $this->container->get('doctrine.dbal.default_connection')->executeQuery('REPLACE INTO tags(`text`) VALUES(?)', [$tag['text']]);
                $tags .= $tag['text'] . ',';
            }

            $data['tags'] = \substr($tags, 0, -1);
        } else {
            $data['tags'] = '';
        }

        /*
         * If empty id, create new one
         */
        if (empty($data['id'])) {
            $data['slug'] = $this->container->get(Slugify::class)->slugify(empty($data['title_en']) ? $data['title_de'] : $data['title_en']);
            $data['user_id'] = $this->userID;
            $data['date'] = \time();
            $this->container->get('doctrine.dbal.default_connection')->insert('blog', $data);

            $postId = $this->container->get('doctrine.dbal.default_connection')->lastInsertId();

            $this->container->get('rewrite_manager')->addRewrite('blog/' . $data['slug'], 'blog', 'detail', [
                'postID' => $postId,
            ]);
            $data['id'] = $postId;
        } else {
            $this->container->get('doctrine.dbal.default_connection')->update('blog', $data, ['id' => $data['id']]);
        }

        return $data;
    }

    protected function delete($id)
    {
        $this->container->get('doctrine.dbal.default_connection')->delete('blog', ['id' => $id]);
        $this->container->get('doctrine.dbal.default_connection')->delete('likes', ['table' => 'blog', 'table_id' => $id]);
    }
}
