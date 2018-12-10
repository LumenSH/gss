<?php

namespace GSS\Controller\Frontend;

use GSS\Component\HttpKernel\Controller;
use Symfony\Component\Routing\Annotation\Route;

class BrowseController extends Controller
{
    /**
     * @Route(path="/browse")
     */
    public function index()
    {
        $servers = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT
  gameserver_browse.*,
  gameserver.slot,
  CONCAT(g.ip, \':\', gameserver.port) as connect,
  products.name as productName
FROM gameserver_browse
INNER JOIN gameserver ON(gameserver.id = gameserver_browse.serverID)
INNER JOIN gameroot_ip g ON gameserver.gameRootIpID = g.id
INNER JOIN products ON(products.id = gameserver.productID)
WHERE gameserver_browse.online = 1
ORDER BY gameserver_browse.cur_players DESC');

        $this->View()->setPageTitle('Server Browse');

        $date = \date('Y-m-d H:i:s');
        $cache = $this->container->get('cache.app')->getItem('server_browse.time');
        if ($cache->isHit()) {
            $date = $cache->get();
        }

        return $this->render('frontend/browse/index.twig', ['servers' => $servers, 'lastUpdate' => $date]);
    }
}
