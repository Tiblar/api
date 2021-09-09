<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210211192404 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, client_secret VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX application_user_id_idx (user_id), INDEX application_client_id_idx (id, client_secret), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_access_token (id VARCHAR(255) NOT NULL, resource_user_id VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, scopes JSON NOT NULL, revoked TINYINT(1) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_F7FA86A4FACE347D (resource_user_id), INDEX oauth_access_token_idx (token, client_id, revoked), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_code (id VARCHAR(255) NOT NULL, resource_user_id VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, state VARCHAR(255) DEFAULT NULL, scopes JSON NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_7C5CF309FACE347D (resource_user_id), INDEX oauth_code_idx (code, client_id, state), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_redirect_url (id VARCHAR(255) NOT NULL, client_id VARCHAR(255) DEFAULT NULL, url VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_D623665B19EB6921 (client_id), INDEX redirect_url_idx (client_id, url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_refresh_token (id VARCHAR(255) NOT NULL, access_token_id VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX oauth_refresh_token_idx (access_token_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth_access_token ADD CONSTRAINT FK_F7FA86A4FACE347D FOREIGN KEY (resource_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE oauth_code ADD CONSTRAINT FK_7C5CF309FACE347D FOREIGN KEY (resource_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE oauth_redirect_url ADD CONSTRAINT FK_D623665B19EB6921 FOREIGN KEY (client_id) REFERENCES application (id)');
        $this->addSql('ALTER TABLE oauth_refresh_token ADD CONSTRAINT FK_55DCF7552CCB2688 FOREIGN KEY (access_token_id) REFERENCES oauth_access_token (id)');
        $this->addSql('ALTER TABLE user_password_reset_token ADD code VARCHAR(255) NOT NULL, ADD expire_timestamp DATETIME NOT NULL, ADD timestamp DATETIME NOT NULL, DROP service, DROP account, DROP link, CHANGE id id VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX user_password_reset_code_idx ON user_password_reset_token (code)');
        $this->addSql('ALTER TABLE user_password_reset_token RENAME INDEX user_id TO user_password_reset_user_id_idx');
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
        $this->addSql('DROP INDEX user_password_reset_code_idx ON user_password_reset_token');
        $this->addSql('ALTER TABLE user_password_reset_token ADD account VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD link VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP expire_timestamp, DROP timestamp, CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE code service VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_password_reset_token RENAME INDEX user_password_reset_user_id_idx TO user_id');
    }
}
