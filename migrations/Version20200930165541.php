<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200930165541 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        if(!$schema->hasTable('post_report')) {
            $this->addSql('CREATE TABLE post_report (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, accepted TINYINT(1) NOT NULL, rejected TINYINT(1) NOT NULL, timestamp DATETIME NOT NULL, INDEX post_id (post_id), INDEX user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        $this->addSql('CREATE INDEX tag_list_idx ON tag_list (post)');
        $this->addSql('CREATE INDEX tag_list_tag_idx ON tag_list (tag)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE post_report');
        $this->addSql('DROP INDEX tag_list_idx ON tag_list');
        $this->addSql('DROP INDEX tag_list_tag_idx ON tag_list');
    }
}
