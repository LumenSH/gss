<?php

namespace GSS\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class DbCleanUpCommand
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class DbCleanUpCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function configure()
    {
        $this->setName('gs:db:cleanup')
            ->setDescription('Clean up');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cleanupRewrites($output);
    }

    /**
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function cleanupRewrites(OutputInterface $output)
    {
        $output->writeln('Searching for dead rewrites');
        $connection = $this->container->get('doctrine.dbal.default_connection');

        $rewrite = $connection->fetchAll('SELECT * FROM core_rewrite');
        $deleteIds = [];

        $progress = new ProgressBar($output);
        $progress->start(\count($rewrite));

        foreach ($rewrite as $item) {
            $forwardParams = \json_decode($item['forwardParams'], true);

            $active = 0;
            switch ($item['forwardController']) {
                case 'user':
                    $active = $connection->fetchColumn('SELECT 1 FROM users WHERE id = ?', [$forwardParams['userID']]);
                    break;
                case 'cms':
                    $active = $connection->fetchColumn('SELECT 1 FROM cms WHERE id = ?', [$forwardParams['cmsID']]);
                    break;
                case 'support':
                    $active = $connection->fetchColumn('SELECT 1 FROM tickets WHERE id = ?', [$forwardParams['ticketID']]);
                    break;
                case 'blog':
                    $active = $connection->fetchColumn('SELECT 1 FROM blog WHERE id = ?', [$forwardParams['postID']]);
                    break;
                case 'forum':
                    if ($item['forwardAction'] === 'thread') {
                        $active = $connection->fetchColumn('SELECT 1 FROM forum_thread WHERE id = ?', [$forwardParams['threadID']]);
                    } else {
                        $active = $connection->fetchColumn('SELECT 1 FROM forum_board WHERE id = ?', [$forwardParams['boardID']]);
                    }
                    break;
            }

            if (!$active) {
                $deleteIds[] = $item['id'];
            }
            $progress->advance();
        }

        $connection->executeQuery('DELETE FROM core_rewrite WHERE id IN(:ids)', ['ids' => $deleteIds], ['ids' => Connection::PARAM_INT_ARRAY]);

        // Refresh Rewrite Cache
        $this->container->get('rewrite_manager')->reloadRewriteCache();

        $progress->finish();
        $output->writeln('');
    }
}
