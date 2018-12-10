<?php

namespace GSS\Component\Cron;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ServerReminder implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function start(): bool
    {
        $sql = '
            SELECT * FROM gameserver
            LEFT JOIN users ON(users.id = gameserver.userID)
            WHERE gameserver.Duration < ? AND gameserver.Duration > ? AND gameserver.Typ = 0
        ';

        /*
         * 7 Days
         */
        $day7 = $this->container->get('doctrine.dbal.default_connection')->fetchAll($sql, [
            \strtotime('+7days'),
            \strtotime('+6days'),
        ]);

        /*
         * 3 Days
         */
        $day3 = $this->container->get('doctrine.dbal.default_connection')->fetchAll($sql, [
            \strtotime('+3days'),
            \strtotime('+2days'),
        ]);

        $this->processReminderMail('email/reminder7days.twig', $day7, 7);
        $this->processReminderMail('email/reminder3days.twig', $day3, 7);

        return true;
    }

    private function processReminderMail($template, $players, $days)
    {
        foreach ($players as $player) {
            $html = $this->container->get('twig')->render($template, [
                'User' => $player,
            ]);

            $mail = new \Swift_Message();
            $mail->setFrom($this->container->getParameter('email.sender'), $this->container->getParameter('email.sendername'))
                ->setTo($player['Email'], $player['Username'])
                ->setSubject(__('Server läuft in ' . $days . ' Tagen ab', 'Mail', 'ReminderSubject' . $days, $player['Language']))
                ->setBody($html, 'text/html', 'UTF-8');

            $this->container->get('mailer')->send($mail);
        }
    }
}
