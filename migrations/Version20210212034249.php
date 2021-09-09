<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210212034249 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oauth_access_token DROP FOREIGN KEY FK_F7FA86A4FACE347D');
        $this->addSql('DROP INDEX IDX_F7FA86A4FACE347D ON oauth_access_token');
        $this->addSql('ALTER TABLE oauth_access_token ADD user_id VARCHAR(255) NOT NULL, DROP resource_user_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oauth_access_token ADD resource_user_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP user_id');
        $this->addSql('ALTER TABLE oauth_access_token ADD CONSTRAINT FK_F7FA86A4FACE347D FOREIGN KEY (resource_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F7FA86A4FACE347D ON oauth_access_token (resource_user_id)');
    }
}
