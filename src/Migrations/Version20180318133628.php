<?php declare(strict_types=1);

namespace GSS\Component\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180318133628 extends AbstractMigration
{
    /**
     * @param Schema $schema
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function up(Schema $schema)
    {
        $this->connection->exec('ALTER TABLE `blog` DROP COLUMN `mode`;');
    }

    public function down(Schema $schema)
    {
    }
}
