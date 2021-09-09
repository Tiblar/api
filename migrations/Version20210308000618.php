<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210308000618 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX log_idx ON analytics_view_log');
        $this->addSql('ALTER TABLE analytics_view_log ADD user_id VARCHAR(255) NOT NULL, ADD expire_timestamp DATETIME NOT NULL');
        $this->addSql('CREATE INDEX log_user_idx ON analytics_view_log (user_id, timestamp)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX log_user_idx ON analytics_view_log');
        $this->addSql('ALTER TABLE analytics_view_log DROP user_id, DROP expire_timestamp');
        $this->addSql('CREATE INDEX log_idx ON analytics_view_log (resource_id, ip_address, type, timestamp)');
    }
}
