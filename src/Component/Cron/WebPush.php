<?php

namespace GSS\Component\Cron;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class WebPush implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function start(): bool
    {
        $sql = '
            SELECT gameserver.*, users.Language, users.id as userID FROM gameserver
            LEFT JOIN users ON(users.id = gameserver.userID)
            WHERE gameserver.Duration < ? AND gameserver.Typ = 0
        ';

        $users = $this->container->get('doctrine.dbal.default_connection')->fetchAll($sql, [
            \strtotime('+7days'),
        ]);

        foreach ($users as $user) {
            $title = __('Ablauf des Gameservers', 'WebPush', 'RemindServerDeleteTitle', $user['Language']);
            $message = __('Dein Gameserver auf dem Port %d läuft in %d Tag(en) ab.', 'RemindServerDeleteMessage', 'WebPush', $user['Language']);

            $curDate = new \DateTime();
            $gsDate = new \DateTime(\date('Y-m-d H:i:s', $user['duration']));
            $dateDiff = $gsDate->diff($curDate);

            $days = (int) $dateDiff->days;

            if ($days == 0) {
                $days = 1;
            }

            $message = \sprintf($message, $user['port'], $days);

            $this->container->get('push.manager')->sendMessage($user['userID'], $title, $message);
        }

        return true;
    }
}
