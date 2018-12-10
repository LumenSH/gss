<?php

namespace GSS\Controller\Backend;

use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Menu.
 */
class MenuController extends Backend
{
    /**
     * @Route("/backend/menu")
     * @Route("/backend/menu/")
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
        return $this->listQuery('core_menu', 99999, $page, $sorting, $filter, $search);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    protected function getOne($id)
    {
        return $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM core_menu WHERE id = ?', [$id]);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    protected function delete($id)
    {
        return $this->container->get('doctrine.dbal.default_connection')->delete('core_menu', ['id' => $id]);
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array|void
     */
    protected function save($id, $data)
    {
        if ($id == null) {
            $this->container->get('doctrine.dbal.default_connection')->insert('core_menu', $data);
        } else {
            $this->container->get('doctrine.dbal.default_connection')->update('core_menu', $data, ['id' => $id]);
        }
    }
}
