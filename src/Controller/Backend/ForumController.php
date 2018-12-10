<?php

namespace GSS\Controller\Backend;

use Symfony\Component\Routing\Annotation\Route;

class ForumController extends Backend
{
    /**
     * @Route("/backend/forum/")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return parent::indexAction();
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
        return $this->listQuery('forum_board', $limit, $page, $sorting, $filter, $search);
    }

    /**
     * @param $id
     *
     * @return array|void
     */
    protected function delete($id)
    {
        $this->container->get('doctrine.dbal.default_connection')->delete('forum_board', ['id' => $id]);
        $this->container->get('rewrite_manager')->removeRewriteByParams([
            'boardID' => $id,
        ]);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    protected function getOne($id)
    {
        $data = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM forum_board WHERE id = ?', [$id]);

        $data['slug'] = $this->container->get('rewrite_manager')->getRewriteByParams([
            'boardID' => $data['id'],
        ]);

        if (\is_array($data['slug'])) {
            $data['slug'] = $data['slug']['link'];
        } else {
            $data['slug'] = '';
        }

        return $data;
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array|void
     */
    protected function save($id, $data)
    {
        $slug = $data['slug'];
        unset($data['slug']);

        if ($id) {
            $this->container->get('doctrine.dbal.default_connection')->update('forum_board', $data, ['id' => $id]);

            $oldSlug = $this->container->get('rewrite_manager')->getRewriteByParams([
                'boardID' => $id,
            ]);

            if (\is_array($oldSlug)) {
                $oldSlug = $oldSlug['link'];
            } else {
                $oldSlug = '';
            }

            if ($oldSlug != $slug) {
                $this->container->get('rewrite_manager')->removeRewriteByParams([
                    'boardID' => $id,
                ]);
                $this->container->get('rewrite_manager')->addRewrite($slug, 'forum', 'board', [
                    'boardID' => $id,
                ]);
            }
        } else {
            $this->container->get('doctrine.dbal.default_connection')->insert('forum_board', $data);
            $insertId = $this->container->get('doctrine.dbal.default_connection')->lastInsertId();
            $this->container->get('rewrite_manager')->addRewrite($slug, 'forum', 'board', [
                'boardID' => $insertId,
            ]);
        }
    }
}
