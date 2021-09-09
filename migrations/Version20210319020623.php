<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210319020623 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_list (id VARCHAR(255) NOT NULL, list_user_id VARCHAR(255) DEFAULT NULL, title VARCHAR(50) NOT NULL, description VARCHAR(400) DEFAULT NULL, visibility VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_8D9D9F137B210E98 (list_user_id), INDEX post_list_idx (list_user_id, visibility), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_list_item (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, list_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX post_list_item_idx (id, user_id), INDEX post_list_item_post_idx (post_id), INDEX post_list_item_user_idx (id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post_list ADD CONSTRAINT FK_8D9D9F137B210E98 FOREIGN KEY (list_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE post_list');
        $this->addSql('DROP TABLE post_list_item');
    }
}
