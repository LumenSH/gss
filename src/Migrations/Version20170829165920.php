<?php

namespace GSS\Component\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170829165920 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->connection->exec('DROP TABLE phinxlog');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
