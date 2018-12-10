<?php

use Codeception\Stub;
use Doctrine\DBAL\Connection;
use GSS\Component\Api\RateLimiting;
use GSS\Component\Hosting\Gameserver\Daemon;
use GSS\Component\Hosting\Gameserver\Games\CSGO;
use Symfony\Component\DependencyInjection\Container;

class RateLimitingTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @throws Exception
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function testRateLimiting()
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('insert')
            ->willReturn(true);

        $rateLimiting = Stub::make(RateLimiting::class, ['connection' => $connection]);
        $container = Stub::make(Container::class, ['get' => $rateLimiting]);

        $gsData = [
            'id' => 1,
            'typ' => 1
        ];

        $daemon = Stub::make(Daemon::class, ['stopServer' => true]);

        /** @var CSGO $server */
        $server = Stub::make(CSGO::class, ['getDaemon' => $daemon, 'gsData' => $gsData, 'container' => $container]);

        $this->assertEquals(1, $server->getId());

        $server->stop();
    }
}