<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210211221305 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oauth_code DROP FOREIGN KEY FK_7C5CF309FACE347D');
        $this->addSql('DROP INDEX IDX_7C5CF309FACE347D ON oauth_code');
        $this->addSql('ALTER TABLE oauth_code ADD user_id VARCHAR(255) NOT NULL, ADD expire_timestamp DATETIME NOT NULL, DROP resource_user_id');
        $this->addSql('CREATE INDEX oauth_user_id_idx ON oauth_code (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX oauth_user_id_idx ON oauth_code');
        $this->addSql('ALTER TABLE oauth_code ADD resource_user_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP user_id, DROP expire_timestamp');
        $this->addSql('ALTER TABLE oauth_code ADD CONSTRAINT FK_7C5CF309FACE347D FOREIGN KEY (resource_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_7C5CF309FACE347D ON oauth_code (resource_user_id)');
    }
}
