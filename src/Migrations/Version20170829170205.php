<?php

namespace GSS\Component\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170829170205 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->connection->exec('ALTER TABLE `gameserver` ADD COLUMN `name` VARCHAR(255) NOT NULL DEFAULT \'0\' AFTER `userID`;');

        /**
         * Fix default server names
         */
        $servers = $this->connection->fetchAll('SELECT gameserver.id, gameserver.port, gameroot_ip.ip, products.internalName FROM gameserver JOIN gameroot_ip ON(gameroot_ip.id = gameserver.gameRootIpID) JOIN products ON(products.id = gameserver.productID)');
        foreach ($servers as $server) {
            $this->connection->update('gameserver', ['name' => \sprintf('(%s) %s:%s', $server['internalName'], $server['ip'], $server['port'])], ['id' => $server['id']]);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
