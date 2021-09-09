<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210521153821 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_category (post_id VARCHAR(255) NOT NULL, category_id VARCHAR(255) NOT NULL, INDEX IDX_B9A190604B89032C (post_id), INDEX IDX_B9A1906012469DE2 (category_id), PRIMARY KEY(post_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post_category ADD CONSTRAINT FK_B9A190604B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_category ADD CONSTRAINT FK_B9A1906012469DE2 FOREIGN KEY (category_id) REFERENCES video_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D30C42C43');
        $this->addSql('DROP INDEX IDX_5A8A6C8D30C42C43 ON post');
        $this->addSql('ALTER TABLE post DROP video_category_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE post_category');
        $this->addSql('ALTER TABLE post ADD video_category_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D30C42C43 FOREIGN KEY (video_category_id) REFERENCES video_category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D30C42C43 ON post (video_category_id)');
    }
}
