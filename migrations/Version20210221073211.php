<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210221073211 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if(!$schema->hasTable("application") == true) {
            $this->addSql('CREATE TABLE application (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, client_secret VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX application_user_id_idx (user_id), INDEX application_client_id_idx (id, client_secret), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("oauth_access_token") == true) {
            $this->addSql('CREATE TABLE oauth_access_token (id VARCHAR(255) NOT NULL, client_id VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, scopes JSON NOT NULL, user_id VARCHAR(255) NOT NULL, revoked TINYINT(1) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX oauth_access_token_idx (token, client_id, revoked), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("oauth_code") == true) {
            $this->addSql('CREATE TABLE oauth_code (id VARCHAR(255) NOT NULL, client_id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, state VARCHAR(255) DEFAULT NULL, scopes JSON NOT NULL, user_id VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX oauth_code_idx (code, client_id, state), INDEX oauth_user_id_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("oauth_redirect_url") == true) {
            $this->addSql('CREATE TABLE oauth_redirect_url (id VARCHAR(255) NOT NULL, client_id VARCHAR(255) DEFAULT NULL, url VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_D623665B19EB6921 (client_id), INDEX redirect_url_idx (client_id, url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("oauth_refresh_token") == true) {
            $this->addSql('CREATE TABLE oauth_refresh_token (id VARCHAR(255) NOT NULL, access_token_id VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX oauth_refresh_token_idx (access_token_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("oauth_redirect_url") == true) {
            $this->addSql('ALTER TABLE oauth_redirect_url ADD CONSTRAINT FK_D623665B19EB6921 FOREIGN KEY (client_id) REFERENCES application (id)');
        }

        if(!$schema->hasTable("oauth_refresh_token") == true) {
            $this->addSql('ALTER TABLE oauth_refresh_token ADD CONSTRAINT FK_55DCF7552CCB2688 FOREIGN KEY (access_token_id) REFERENCES oauth_access_token (id)');
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oauth_redirect_url DROP FOREIGN KEY FK_D623665B19EB6921');
        $this->addSql('ALTER TABLE oauth_refresh_token DROP FOREIGN KEY FK_55DCF7552CCB2688');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE oauth_access_token');
        $this->addSql('DROP TABLE oauth_code');
        $this->addSql('DROP TABLE oauth_redirect_url');
        $this->addSql('DROP TABLE oauth_refresh_token');
    }
}