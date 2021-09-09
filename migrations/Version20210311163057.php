<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210311163057 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX log_user_idx ON analytics_view_log');
        $this->addSql('CREATE INDEX log_ip_address_idx ON analytics_view_log (ip_address, timestamp)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX log_ip_address_idx ON analytics_view_log');
        $this->addSql('CREATE INDEX log_user_idx ON analytics_view_log (user_id, timestamp)');
    }
}
