<?php

namespace GSS\Controller\Backend;

use GSS\Component\Gameserver;
use GSS\Component\Util;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Server.
 */
class ServerController extends Backend
{
    /**
     * @Route("/backend/server/")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * @Route("/backend/server/getOptions")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getOptionsAction()
    {
        return new JsonResponse([
            'games' => $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT name, id FROM products'),
        ]);
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
        $sql = $this->container->get('doctrine.dbal.default_connection')->createQueryBuilder()
            ->from('gameserver', 'gameserver')
            ->leftJoin('gameserver', 'users', 'users', 'users.id = gameserver.userID')
            ->leftJoin('gameserver', 'products', 'products', 'products.id = gameserver.productID')
            ->leftJoin('gameserver', 'gameroot_ip', 'gameroot_ip', 'gameroot_ip.id = gameserver.gameRootIpID')
            ->addSelect('gameserver.*')
            ->addSelect('users.Username')
            ->addSelect('products.name as Gamename')
            ->addSelect('gameroot_ip.IP')
            ->addOrderBy('gameserver.id', 'ASC');

        if (isset($search['type'])) {
            $sql
                ->andWhere('gameserver.productID = :game')
                ->setParameter('game', $search['type']['id']);
        }

        if (isset($search['port'])) {
            $sql
                ->andWhere('gameserver.port = :port')
                ->setParameter('port', $search['port']);
        }

        if (isset($search['Typ']) && $search['Typ'] != '-1') {
            $sql
                ->andWhere('gameserver.typ = :typ')
                ->setParameter('typ', $search['Typ']);
        }

        $countSQL = clone $sql;
        $count = (int) $countSQL
            ->select('COUNT(*) as count')
            ->execute()
            ->fetchColumn();

        $sql
            ->setMaxResults($limit)
            ->setFirstResult(Util::getSqlOffset($page, $limit));

        $data = $sql->execute()->fetchAll();
        $pageination = [];

        for ($i = 1; $i <= \ceil($count / $limit); ++$i) {
            $pageination[] = $i;
        }

        return [
            'data' => $data,
            'totalCount' => $count,
            'pages' => \ceil($count / $limit),
            'pageination' => $pageination,
        ];
    }
}
