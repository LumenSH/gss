<?php

namespace GSS\Controller\Backend;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Products.
 */
class ProductsController extends Backend
{
    /**
     * @Route("/backend/products")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * @Route("/backend/products/version")
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function versionAction()
    {
        if ($this->Request()->isDelete()) {
            $id = $this->Request()->get('id');

            $this->container->get('doctrine.dbal.default_connection')->delete('products_version', ['id' => $id]);

            return new JsonResponse();
        }

        if ($this->Request()->isPost()) {
            $params = $this->Request()->getAjaxPost();

            $this->container->get('doctrine.dbal.default_connection')->insert('products_version', $params);
            $params['id'] = $this->container->get('doctrine.dbal.default_connection')->lastInsertId();

            return new JsonResponse(['data' => $params]);
        }
    }

    /**
     * @Route("/backend/products/variant")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function variantAction()
    {
        if ($this->Request()->isGet()) {
            $id = $this->Request()->get('id');

            $data = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM products_sub WHERE id = ?', [$id]);

            return new JsonResponse($data);
        }

        if ($this->Request()->isPost()) {
            $params = $this->Request()->getAjaxPost();

            if (empty($params['id'])) {
                $this->container->get('doctrine.dbal.default_connection')->insert('products_sub', $params);
            } else {
                $this->container->get('doctrine.dbal.default_connection')->update('products_sub', $params, ['id' => $params['id']]);
            }
        } elseif ($this->Request()->isDelete()) {
            $id = $this->Request()->get('id');

            $this->container->get('doctrine.dbal.default_connection')->delete('products_sub', ['id' => $id]);
        }

        return new JsonResponse();
    }

    /**
     * @Route("/backend/products/saveImage")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveImageAction()
    {
        if (!\file_exists($this->container->getParameter('kernel.public_dir') . '/uploads/games/')) {
            \mkdir($this->container->getParameter('kernel.public_dir') . '/uploads/games/', 0777, true);
        }

        $productId = $this->Request()->getPost('id');

        $img = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT img FROM products WHERE id = ?', [$productId]);

        if (!empty($img)) {
            if (\file_exists($this->container->getParameter('kernel.public_dir') . '/uploads/games/' . $img)) {
                @\unlink($this->container->getParameter('kernel.public_dir') . '/uploads/games/' . $img);
            }
        }

        if (!empty($_FILES['img']['name'])) {
            $extension = \pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
            $fileName = \uniqid('gss', true) . '.' . $extension;

            if (!\file_exists($this->container->getParameter('kernel.public_dir') . '/uploads/games/')) {
                if (!\mkdir($this->container->getParameter('kernel.public_dir') . '/uploads/games/', 0777, true) && !\is_dir($this->container->getParameter('kernel.public_dir') . '/uploads/games/')) {
                    throw new \RuntimeException(\sprintf('Directory "%s" was not created', $this->container->getParameter('kernel.public_dir') . '/uploads/games/'));
                }
            }

            \move_uploaded_file($_FILES['img']['tmp_name'], $this->container->getParameter('kernel.public_dir') . '/uploads/games/' . $fileName);

            $this->container->get('doctrine.dbal.default_connection')->update('products', ['img' => $fileName], ['id' => $productId]);
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
        return $this->listQuery('products', 9999, $page, $sorting, $filter, $search);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    protected function getOne($id)
    {
        $singleProduct = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM products WHERE id = ?', [
            $id,
        ]);

        $singleProduct['variants'] = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM products_sub WHERE productID = ?', [$id]);
        $singleProduct['versions'] = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM products_version WHERE productID = ?', [$id]);

        return $singleProduct;
    }

    /**
     * @param $id
     * @param $data
     *
     * @return mixed
     */
    protected function save($id, $data)
    {
        if ($id != null) {
            $this->container->get('doctrine.dbal.default_connection')->update('products', $data, ['id' => $id]);
        } else {
            $this->container->get('doctrine.dbal.default_connection')->insert('products', $data);
            $id = $this->container->get('doctrine.dbal.default_connection')->lastInsertId();
            $data['id'] = $id;
        }

        return $data;
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @return array|void
     */
    protected function delete($id)
    {
        $form = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM products WHERE id = ?', [$id]);

        /*
         * Remove Image on Deletion
         */
        if (!empty($form['img'])) {
            if (\file_exists($this->container->getParameter('kernel.public_dir') . '/uploads/games/' . $form['img'])) {
                @\unlink($this->container->getParameter('kernel.public_dir') . '/uploads/games/' . $form['img']);
            }
        }

        $this->container->get('doctrine.dbal.default_connection')->delete('products_sub', ['productID' => $id]);
        $this->container->get('doctrine.dbal.default_connection')->delete('products_version', ['productID' => $id]);
        $this->container->get('doctrine.dbal.default_connection')->delete('products', ['id' => $id]);
    }
}
