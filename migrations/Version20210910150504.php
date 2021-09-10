<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210910150504 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_info CHANGE avatar old_avatar VARCHAR(255)');
        $this->addSql('ALTER TABLE user_info ADD avatar_file_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_info ADD CONSTRAINT FK_B1087D9E45A576B2 FOREIGN KEY (avatar_file_id) REFERENCES file (id)');
        $this->addSql('CREATE INDEX IDX_B1087D9E45A576B2 ON user_info (avatar_file_id)');
        $this->addSql('UPDATE user_info u SET avatar_file_id = (SELECT id FROM file f WHERE f.url = u.old_avatar OR f.hash_name = SUBSTRING_INDEX(u.old_avatar, "/", -1))');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_info DROP FOREIGN KEY FK_B1087D9E45A576B2');
        $this->addSql('DROP INDEX IDX_B1087D9E45A576B2 ON user_info');
        $this->addSql('ALTER TABLE user_info CHANGE old_avatar avatar VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP avatar_file_id');
    }
}
