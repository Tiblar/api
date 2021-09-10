<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210910022804 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX backblaze_file_id_idx ON file_init');
        $this->addSql('ALTER TABLE file_init CHANGE back_blaze_file_id s3_file_id VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX s3_file_id_idx ON file_init (s3_file_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX s3_file_id_idx ON file_init');
        $this->addSql('ALTER TABLE file_init CHANGE s3_file_id back_blaze_file_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE INDEX backblaze_file_id_idx ON file_init (back_blaze_file_id)');
    }
}
