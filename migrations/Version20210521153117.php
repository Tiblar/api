<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210521153117 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD video_category_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D30C42C43 FOREIGN KEY (video_category_id) REFERENCES video_category (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D30C42C43 ON post (video_category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D30C42C43');
        $this->addSql('DROP INDEX IDX_5A8A6C8D30C42C43 ON post');
        $this->addSql('ALTER TABLE post DROP video_category_id');
    }
}
