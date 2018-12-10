<?php

namespace GSS\Component\Routing;

use Doctrine\DBAL\Connection;
use GSS\Component\Traits\CacheExtension;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * Class RewriteManager.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class RewriteManager
{
    use CacheExtension;

    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * RewriteManager constructor.
     *
     * @param AdapterInterface $cache
     * @param Connection       $connection
     */
    public function __construct(AdapterInterface $cache, Connection $connection)
    {
        $this->cache = $cache;
        $this->connection = $connection;
    }

    /**
     * Add a new rewrite Url.
     *
     * @param $baseRewrite
     * @param $controller
     * @param string $action
     * @param array  $arguments
     *
     * @return string
     */
    public function addRewrite($baseRewrite, $controller, $action = 'index', $arguments = [])
    {
        $rewrite = $baseRewrite;
        $counter = 2;

        while ($this->connection->fetchColumn('SELECT 1 FROM core_rewrite WHERE link = ?', [$rewrite]) == 1) {
            $rewrite = $baseRewrite . '-' . $counter;
            ++$counter;
        }

        $this->connection->insert('core_rewrite', [
            'link' => $rewrite,
            'forwardController' => $controller,
            'forwardAction' => $action,
            'forwardParams' => \json_encode($arguments),
        ]);

        $item = $this->cache->getItem(\md5('route_' . $rewrite));
        $item->set([
            'link' => $rewrite,
            'forwardController' => $controller,
            'forwardAction' => $action,
            'forwardParams' => \json_encode($arguments),
        ]);
        $this->cache->save($item);

        $item = $this->cache->getItem($this->getCacheKeyByParams($arguments));
        $item->set([
            'link' => $rewrite,
            'forwardController' => $controller,
            'forwardAction' => $action,
            'forwardParams' => \json_encode($arguments),
        ]);
        $this->cache->save($item);

        return $rewrite;
    }

    /**
     * Remove Rewrite Link.
     *
     * @param $rewrite
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return bool
     */
    public function removeRewrite($rewrite)
    {
        $this->cache->deleteItem(\md5('route_' . $rewrite));

        $this->connection->delete('core_rewrite', ['link' => $rewrite]);

        return true;
    }

    /**
     * @param array $params
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function removeRewriteByParams($params)
    {
        $rewrite = $this->getRewriteByParams($params)['link'];
        $this->removeRewrite($rewrite);
    }

    /**
     * Get a Rewrite Link.
     *
     * @param string $controller
     * @param string $action
     * @param array  $params
     *
     * @return string
     */
    public function getLink($controller, $action, $params = [])
    {
        return $this->connection->fetchColumn('SELECT link FROM core_rewrite WHERE forwardController = ? AND forwardAction = ? AND forwardParams = ?', [
            $controller,
            $action,
            \json_encode($params),
        ]);
    }

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    public function reloadRewriteCache()
    {
        $allRewrites = $this->connection->fetchAll('SELECT * FROM core_rewrite');

        $writeData = [];
        $writeData['route_loaded'] = true;

        foreach ($allRewrites as $rewrite) {
            $decode = \json_decode($rewrite['forwardParams'], true);
            $writeData[\md5('route_' . $rewrite['link'])] = $rewrite;
            $writeData[$this->getCacheKeyByParams($decode)] = $rewrite;
        }

        $this->setMultipleData($this->cache, $writeData);
    }

    /**
     * @param $params
     *
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getRewriteByParams($params)
    {
        $loaded = $this->cache->getItem('route_loaded');

        if (!$loaded->isHit()) {
            $this->reloadRewriteCache();
        }

        return $this->cache->getItem($this->getCacheKeyByParams($params))->get();
    }

    /**
     * @param $url
     *
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getRewriteParamsByUrl($url)
    {
        $loaded = $this->cache->getItem('route_loaded');

        if (!$loaded->isHit()) {
            $this->reloadRewriteCache();
        }

        return $this->cache->getItem(\md5('route_' . $url))->get();
    }

    /**
     * @param $params
     *
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getCacheKeyByParams($params)
    {
        $str = '';
        foreach ($params as $key => $value) {
            $str .= $key . '=' . $value . '_';
        }

        $key = \substr($str, 0, -1);

        return 'route_' . $key;
    }
}
