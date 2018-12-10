<?php declare(strict_types=1);

namespace GSS\Component\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171228181936 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->connection->exec('CREATE TABLE IF NOT EXISTS `gameserver_browse` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`serverID` INT(11) NOT NULL,
	`online` TINYINT(4) NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`cur_players` INT(4) NOT NULL,
	`gamemode` VARCHAR(100) NOT NULL,
	`gametype` VARCHAR(100) NOT NULL,
	`gamemap` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB
;
');

        $this->connection->exec('INSERT INTO `crontab` (`id`, `Name`, `Action`, `LastExecute`, `NextExecute`, `Time`) VALUES (NULL, \'Update Server Browser\', \'updatebrowser\', 0, 1514481302, 900);');

        $this->connection->exec('INSERT INTO `core_menu` (`menuID`, `menuClass`, `menuDefaultName`, `menuTyp`, `menuSort`, `menuLink`) VALUES (\'browse\', \'icon ion-cloud\', \'Server Browse\', \'2\', \'50\', \'browse\');');
    }

    public function down(Schema $schema)
    {
    }
}
