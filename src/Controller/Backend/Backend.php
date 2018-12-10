<?php

namespace GSS\Controller\Backend;

use Doctrine\DBAL\Query\QueryBuilder;
use GSS\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Backend
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
abstract class Backend extends Controller
{
    /**
     * @return JsonResponse
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function indexAction()
    {
        if ($this->Request()->get('id') && $this->Request()->isGet()) {
            return new JsonResponse($this->getOne($this->Request()->get('id')));
        }

        if ($this->Request()->isDelete()) {
            return new JsonResponse($this->delete($this->Request()->get('id')));
        }

        if ($this->Request()->isPost()) {
            $data = $this->Request()->getAjaxPost();

            if (!empty($data['id']) && (int) $data['id']) {
                return new JsonResponse($this->save($data['id'], $data));
            }

            return new JsonResponse($this->save(null, $data));
        }

        $limit = $this->Request()->get('limit', 25);
        $page = $this->Request()->get('page', 1);
        $sorting = $this->Request()->get('sort', '');
        $filter = $this->Request()->get('filter', []);
        $search = $this->Request()->get('search', []);

        return new JsonResponse($this->getList($limit, $page, $sorting, $filter, $search));
    }

    /**
     * @param $id
     *
     * @return array
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    protected function getOne($id)
    {
        return [];
    }

    /**
     * @param int    $limit
     * @param int    $page
     * @param string $sorting
     * @param array  $filter
     * @param array  $search
     *
     * @return array
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    protected function getList($limit = 25, $page = 1, $sorting = '', $filter = [], $search = [])
    {
        return [];
    }

    /**
     * @param $id
     * @param $data
     *
     * @return array
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    protected function save($id, $data)
    {
        return [];
    }

    /**
     * @param $id
     *
     * @return array
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    protected function delete($id)
    {
        return [];
    }

    /**
     * Helper function to get data for paginated data.
     *
     * @param $table
     * @param int   $limit
     * @param int   $page
     * @param array $sorting
     * @param array $filter
     * @param array $search
     *
     * @return array
     */
    protected function listQuery($table, $limit = 25, $page = 1, $sorting = [], $filter = [], $search = [])
    {
        /** @var QueryBuilder $select */
        $select = $this->container->get('doctrine.dbal.default_connection')->createQueryBuilder()->from($table, $table);

        $select->select('*');

        if (!empty($sorting)) {
            $select->orderBy($sorting['key'], $sorting['order']);
        }

        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                $select->andWhere($key . ' = :' . $key)
                    ->setParameter($key, $value);
            }
        }

        if (!empty($search)) {
            $searchStr = '';
            foreach ($search as $key => $value) {
                $searchStr .= $key . ' LIKE "' . $value . '" OR ';
            }

            $searchStr = \substr($searchStr, 0, -3);

            $select->andWhere($searchStr);
        }

        $countSQL = clone $select;
        $totalCount = $countSQL->select('COUNT(*)')->execute()->fetchColumn();

        $pageination = [];

        for ($i = $page - 3; $i < $page; ++$i) {
            if ($i != $page && $i < $page && $i > 0) {
                $pageination[] = $i;
            }
        }

        $pageination[] = $page;

        for ($i = 1; $i < 4; ++$i) {
            if ($page + $i <= \ceil($totalCount / $limit)) {
                $pageination[] = $page + $i;
            }
        }

        if (!empty($limit)) {
            $select->setMaxResults($limit);
        }

        if (!empty($page)) {
            $select->setFirstResult(($page == 1 ? 0 : ($page - 1) * $limit));
        }

        return [
            'data' => $select->execute()->fetchAll(),
            'totalCount' => $totalCount,
            'pages' => \ceil($totalCount / $limit),
            'pageination' => $pageination,
        ];
    }
}
