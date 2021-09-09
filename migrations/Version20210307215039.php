<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210307215039 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE analytics_post (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, views BIGINT NOT NULL, timestamp DATETIME NOT NULL, INDEX analytics_idx (user_id, post_id, timestamp), INDEX analytics_post_idx (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE analytics_user (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, views BIGINT NOT NULL, timestamp DATETIME NOT NULL, INDEX analytics_idx (user_id, timestamp), INDEX analytics_user_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE analytics_view_log (id VARCHAR(255) NOT NULL, resource_id BIGINT NOT NULL, ip_address VARCHAR(255) NOT NULL, type BIGINT NOT NULL, timestamp DATETIME NOT NULL, INDEX log_idx (resource_id, ip_address, type, timestamp), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE analytics_post');
        $this->addSql('DROP TABLE analytics_user');
        $this->addSql('DROP TABLE analytics_view_log');
    }
}
