<?php

namespace GSS\Component\Cron;

use Exception;
use GSS\Component\Hosting\SSH;
use PDO;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class MTAVersionUpdater
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class MTAVersionUpdater implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const LINUX_64BIT_ID = '#row4x';
    const FILENAME = '/multitheftauto_linux_x64-(?<version>\d+.\d+.\d+)-rc-(?<revision>\d+)/';
    const DOWNLOAD_URL = 'https://nightly.mtasa.com/';

    /**
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function start(): bool
    {
        $availableVersions = $this->getLastVersions();
        $installedVersions = $this->getInstalledVersions();

        /** @var SSH $server */
        foreach ($this->getServersToInstall() as $server) {
            $contents = $server->listContents('/imageserver/games/mta');

            // mta folder does not exist
            if ($contents === false) {
                $server->exec('mkdir -p /imageserver/games/mta');
                $contents = [];
            }

            if (!\in_array('basicconfig', $contents)) {
                $server->exec('cd /imageserver/games/mta && curl https://linux.mtasa.com/dl/baseconfig.tar.gz -o baseconfig.tar.gz && tar xf baseconfig.tar.gz && rm baseconfig.tar.gz');
            }

            foreach ($availableVersions as $availableVersion => $link) {
                if (!\in_array($availableVersion, $installedVersions)) {
                    $this->container->get('doctrine.dbal.default_connection')->insert('products_version', [
                        'productID' => 1,
                        'version' => $availableVersion,
                    ]);
                }
                if (!\in_array($availableVersion, $contents)) {
                    echo "Downloading $availableVersion for host " . $server->getHostname() . "\n";
                    echo $server->exec('mkdir -p /imageserver/games/mta/' . $availableVersion);
                    echo $server->exec('cd /imageserver/games/mta/' . $availableVersion . ' && curl ' . $link . ' -o mtasa.tar.gz && tar xf mtasa.tar.gz && rm mtasa.tar.gz && mv multitheftauto_linux*/* . && rmdir multitheftauto_linux* && cp -R /imageserver/games/mta/baseconfig/* mods/deathmatch');
                }
            }
        }

        return true;
    }

    /**
     * Returns latest versions from nightly page0
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getLastVersions()
    {
        $html = \file_get_contents('https://nightly.mtasa.com/');
        $crawler = new Crawler($html);

        $downloadVersions = [];
        $index = 0;
        while (true) {
            $node = $crawler->filter(self::LINUX_64BIT_ID . $index);
            if ($node->count() === 0) {
                break;
            }

            $fileName = $node->filter('a')->attr('href');
            \preg_match_all(self::FILENAME, $fileName, $matches, PREG_SET_ORDER, 0);
            $versionName = $matches[0]['version'] . '-r' . $matches[0]['revision'];

            $downloadVersions[$versionName] = self::DOWNLOAD_URL . $fileName;
            ++$index;
        }

        return $downloadVersions;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getInstalledVersions()
    {
        return $this->container->get('doctrine.dbal.default_connection')->executeQuery('SELECT version FROM products_version WHERE productID = 1')->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getServersToInstall()
    {
        $rootsDb = $this->container->get('doctrine.dbal.default_connection')->executeQuery('SELECT id FROM gameroot')->fetchAll(PDO::FETCH_COLUMN);
        $roots = [];

        foreach ($rootsDb as $rootItem) {
            try {
                $ssh = new SSH($rootItem);
                $roots[] = $ssh;
            } catch (Exception $e) {
            }
        }

        return $roots;
    }
}
