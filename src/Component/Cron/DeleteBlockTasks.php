<?php

namespace GSS\Component\Cron;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DeleteBlockTasks implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function start(): bool
    {
        $this->container->get('doctrine.dbal.default_connection')->executeQuery('DELETE FROM blocked_tasks WHERE TTL < UNIX_TIMESTAMP()');

        return true;
    }
}
