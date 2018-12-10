<?php

namespace GSS\Component\Traits;

use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * Trait CacheExtension
 */
trait CacheExtension
{
    /**
     * @param AdapterInterface $adapter
     * @param array            $data
     *
     * @return bool
     */
    public function setMultipleData(AdapterInterface $adapter, array $data)
    {
        $items = $adapter->getItems(\array_keys($data));
        /** @var CacheItemInterface $item */
        foreach ($items as $item) {
            $item->set($data[$item->getKey()]);
            $adapter->saveDeferred($item);
        }

        return $adapter->commit();
    }
}
