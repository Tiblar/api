<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210418200703 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD spam TINYINT(1) DEFAULT \'0\' NOT NULL, ADD ip_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX ip_idx ON spam_filter_ip_list');
        $this->addSql('ALTER TABLE spam_filter_ip_list CHANGE ip ip_address VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX ip_idx ON spam_filter_ip_list (ip_address)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP spam, DROP ip_address');
        $this->addSql('DROP INDEX ip_idx ON spam_filter_ip_list');
        $this->addSql('ALTER TABLE spam_filter_ip_list CHANGE ip_address ip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE INDEX ip_idx ON spam_filter_ip_list (ip)');
    }
}
