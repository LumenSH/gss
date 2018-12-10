<?php

namespace GSS\Component\Cron;

use GameQ\GameQ;
use GSS\Component\Hosting\GameQUtil;
use GSS\Component\Structs\GameQResult;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class GameserverBanner implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $publicDir;

    public function start(): bool
    {
        $this->publicDir = $this->container->getParameter('kernel.public_dir');
        $gameservers = $this->container->get('doctrine.dbal.default_connection')->fetchAll('
            SELECT gameserver.id, products.internalName as Game, gameserver.port as Port, gameroot_ip.IP FROM gameserver
            INNER JOIN gameroot_ip ON(gameroot_ip.id = gameserver.gamerootIpID)
            INNER JOIN products ON(products.id = gameserver.productID)
            WHERE products.banner = 1
        ');

        $serverChunks = \array_chunk($gameservers, 10);

        foreach ($serverChunks as $gameservers) {
            $gameQClient = new GameQ();
            $gameQClient->setOption('timeout', 4);
            $gameQClient->addFilter('normalise');

            foreach ($gameservers as $gameserver) {
                GameQUtil::addServer($gameQClient, $gameserver);
            }

            $results = $gameQClient->process();

            foreach ($results as $key => $gameserver) {
                $gameserver = new GameQResult($gameserver);

                if ($gameserver->isOnline()) {
                    $this->container->get('doctrine.dbal.default_connection')->update('gameserver', [
                        'BannerPlayers' => 0,
                    ], ['id' => $key]);
                } else {
                    $this->container->get('doctrine.dbal.default_connection')->update('gameserver', [
                        'BannerPlayers' => $gameserver->getCurrentPlayers(),
                        'BannerName' => $gameserver->getHostname(),
                    ], ['id' => $key]);
                }

                $this->createServerBanner($gameserver, $key);
            }
        }

        return true;
    }

    /**
     * Creates the awesome server banner :-).
     *
     * @param GameQResult $gameserver
     * @param int         $gameserverId
     *
     * @return bool
     */
    private function createServerBanner(GameQResult $gameserver, int $gameserverId)
    {
        $font = $this->publicDir . '/src/fonts/RobotoCondensed-Regular.ttf';
        $fontSize = 10;
        $fontSizeHead = 11;
        $section1Left = 9;
        $section1Right = 51;
        $section2Left = 200;
        $section2Right = 240;

        $line1Height = 48;
        $line2Height = 66;
        $line3Height = 84;

        $im = \imagecreatetruecolor(400, 100);

        $background = \imagecolorallocate($im, 47, 53, 64);
        $backgroundSub = \imagecolorallocate($im, 38, 43, 51);

        $fontLight = \imagecolorallocate($im, 186, 188, 191);
        $fontDark = \imagecolorallocate($im, 130, 134, 140);

        $online = \imagecolorallocate($im, 76, 175, 80);
        $offline = \imagecolorallocate($im, 244, 47, 47);

        \imagefill($im, 0, 0, $background);

        \imagefilledrectangle($im, 0, 0, 400, 28, $backgroundSub);

        $gameserverRow = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM gameserver WHERE id = ?', [$gameserverId]);

        \imagettftext($im, $fontSizeHead, 0, 26, 20, $fontLight, $font, $gameserver->getHostname() ?: $gameserverRow['bannerName']);

        /*
         * Online/Offline
         */
        \imagefilledellipse($im, 13, 14, 6, 6, $gameserver->isOnline() ? $online : $offline);

        /*
         * Left Fields
         */
        \imagettftext($im, $fontSize, 0, $section1Left, $line1Height, $fontDark, $font, 'IP:');
        \imagettftext($im, $fontSize, 0, $section1Right, $line1Height, $fontLight, $font, $gameserver->getAddress() . ':' . $gameserverRow['port']);

        \imagettftext($im, $fontSize, 0, $section1Left, $line2Height, $fontDark, $font, 'Game:');
        \imagettftext($im, $fontSize, 0, $section1Right, $line2Height, $fontLight, $font, $gameserver->getGame());

        \imagettftext($im, $fontSize, 0, $section1Left, $line3Height, $fontDark, $font, 'Mode:');
        \imagettftext($im, $fontSize, 0, $section1Right, $line3Height, $fontLight, $font, $gameserver->getGameType());

        /*
         * Right Fields
         */

        \imagettftext($im, $fontSize, 0, $section2Left, $line1Height, $fontDark, $font, 'Slots:');
        \imagettftext($im, $fontSize, 0, $section2Right, $line1Height, $fontLight, $font, $gameserver->getCurrentPlayers() . '/' . $gameserverRow['slot']);

        \imagettftext($im, $fontSize, 0, $section2Left, $line2Height, $fontDark, $font, 'Map:');
        \imagettftext($im, $fontSize, 0, $section2Right, $line2Height, $fontLight, $font, $gameserver->getMapName());

        /*
         * Footer
         */
        \imagettftext($im, $fontSize, 0, 215, 90, $fontDark, $font, 'Hosted by');
        \imagettftext($im, $fontSize, 0, 273, 90, $fontLight, $font, 'gameserver-sponsor.me');

        $gsLogo = \imagecreatefrompng($this->publicDir . '/src/img/gs_logo_minimal.png');
        \imagecopy($im, $gsLogo, 360, 7, 0, 0, 30, 14);

        if (!\file_exists($this->publicDir . 'banner/')) {
            if (!\mkdir($this->publicDir . 'banner/') && !\is_dir($this->publicDir . 'banner/')) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $this->publicDir . 'banner/'));
            }
        }

        \imagepng($im, $this->publicDir . 'banner/' . \str_replace('.', '_', $gameserver->getAddress()) . '_' . $gameserverRow['port'] . '.png');
        \imagedestroy($im);

        return true;
    }
}
