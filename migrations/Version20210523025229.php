<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210523025229 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE thumbnail (id VARCHAR(255) NOT NULL, thumbanil_attachment_id VARCHAR(255) DEFAULT NULL, attachment_file_id VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_C35726E6F9677BF (thumbanil_attachment_id), INDEX IDX_C35726E65B5E2CEA (attachment_file_id), INDEX thumbnail_idx (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE thumbnail ADD CONSTRAINT FK_C35726E6F9677BF FOREIGN KEY (thumbanil_attachment_id) REFERENCES attachment (id)');
        $this->addSql('ALTER TABLE thumbnail ADD CONSTRAINT FK_C35726E65B5E2CEA FOREIGN KEY (attachment_file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE file ADD duration INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE thumbnail');
        $this->addSql('ALTER TABLE file DROP duration');
    }
}
