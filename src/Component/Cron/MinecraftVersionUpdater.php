<?php

namespace GSS\Component\Cron;

use Exception;
use GSS\Component\Hosting\SSH;
use PDO;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DomCrawler\Crawler;

class MinecraftVersionUpdater implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function start(): bool
    {
        $currentVersions = $this->container->get('doctrine.dbal.default_connection')->executeQuery('SELECT version FROm products_version WHERE productID = 6')->fetchAll(PDO::FETCH_COLUMN);

        $html = \file_get_contents('https://mcversions.net');

        $crawler = new Crawler($html);

        /**
         * Load Roots.
         */
        $rootsDb = $this->container->get('doctrine.dbal.default_connection')->executeQuery('SELECT id FROM gameroot')->fetchAll(PDO::FETCH_COLUMN);
        $roots = [];

        foreach ($rootsDb as $rootItem) {
            try {
                $ssh = new SSH($rootItem);
                $roots[] = $ssh;
            } catch (Exception $e) {
            }
        }

        $versionsToDownload = [];

        $items = $crawler->filter('#content .container .col-md-3');

        for ($i = 0; $i <= $items->count() - 1; ++$i) {
            $versions = $items->eq($i)->filter('.list-group-item');
            for ($k = 0; $k <= $versions->count() - 1; ++$k) {
                $version = $versions->eq($k);
                $versionName = $version->filter('.version')->text();

                try {
                    $versionsToDownload[$versionName] = $version->filter('.downloads a.server')->attr('href');
                } catch (Exception $e) {
                }
            }
        }

        $this->handleDownloads($roots, $versionsToDownload, $currentVersions);

        return true;
    }

    /**
     * Download Minecraft Versions for the gameservers.
     *
     * @param array $roots
     * @param array $versions
     * @param array $currentVersions
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function handleDownloads($roots, $versions, $currentVersions)
    {
        /** @var SSH $root */
        foreach ($roots as $root) {
            $contents = $root->listContents('/imageserver/games/mc');

            // mc folder does not exist
            if ($contents === false) {
                $root->exec('mkdir /imageserver/games/mc');
                $contents = [];
            }

            foreach ($versions as $versionName => $versionDownload) {
                if (!\in_array($versionName, $contents)) {
                    echo "Downloading $versionName for host " . $root->getHostname() . "\n";
                    $root->exec('mkdir -p /imageserver/games/mc/' . $versionName);
                    $root->exec('curl ' . $versionDownload . ' -o /imageserver/games/mc/' . $versionName . '/minecraft.jar');
                    $root->exec('chmod +x /imageserver/games/mc/' . $versionName . '/minecraft.jar');
                }
            }
        }

        foreach ($versions as $versionName => $versionDownload) {
            if (!\in_array($versionName, $currentVersions)) {
                $this->container->get('doctrine.dbal.default_connection')->insert('products_version', [
                    'productID' => 6,
                    'version' => $versionName,
                ]);
            }
        }
    }
}
