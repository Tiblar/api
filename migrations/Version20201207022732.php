<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201207022732 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_service_connection CHANGE link link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE billing_product CHANGE subscription_duration subscription_frequency JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE billing_order ADD frequency VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE billing_order DROP frequency');
        $this->addSql('ALTER TABLE billing_product CHANGE subscription_frequency subscription_duration JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE user_service_connection CHANGE link link VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
