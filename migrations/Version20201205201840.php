<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201205201840 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE billing_product_attribute ADD product_attribute_id VARCHAR(255) DEFAULT NULL, DROP product_id');
        $this->addSql('ALTER TABLE billing_product_attribute ADD CONSTRAINT FK_A85A3BAF3B420C91 FOREIGN KEY (product_attribute_id) REFERENCES billing_product (id)');
        $this->addSql('CREATE INDEX IDX_A85A3BAF3B420C91 ON billing_product_attribute (product_attribute_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE billing_product_attribute DROP FOREIGN KEY FK_A85A3BAF3B420C91');
        $this->addSql('DROP INDEX IDX_A85A3BAF3B420C91 ON billing_product_attribute');
        $this->addSql('ALTER TABLE billing_product_attribute ADD product_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP product_attribute_id');
    }
}
