<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201227172840 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE file_part (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, file_init_id VARCHAR(255) NOT NULL, part INT NOT NULL, part_size NUMERIC(16, 8) NOT NULL, hash VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (user_id), INDEX file_id_idx (file_init_id, part), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_init (id VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, part_count INT NOT NULL, upload_url VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, extension VARCHAR(255) NOT NULL, original_name VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (user_id, status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE file ADD extension VARCHAR(255) NOT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE file_part');
        $this->addSql('DROP TABLE file_init');
        $this->addSql('ALTER TABLE file DROP extension, CHANGE url url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
