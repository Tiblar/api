<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201209012425 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX order_id_idx ON billing_billing_attribute');
        $this->addSql('ALTER TABLE billing_billing_attribute ADD order_attributes_id VARCHAR(255) DEFAULT NULL, DROP order_id');
        $this->addSql('ALTER TABLE billing_billing_attribute ADD CONSTRAINT FK_72E9EF7C17CC08DB FOREIGN KEY (order_attributes_id) REFERENCES billing_order (id)');
        $this->addSql('CREATE INDEX order_id_idx ON billing_billing_attribute (order_attributes_id)');
        $this->addSql('DROP INDEX order_id_idx ON billing_invoice');
        $this->addSql('ALTER TABLE billing_invoice ADD order_invoices_id VARCHAR(255) DEFAULT NULL, DROP order_id');
        $this->addSql('ALTER TABLE billing_invoice ADD CONSTRAINT FK_FB4B9C933FE6C5F3 FOREIGN KEY (order_invoices_id) REFERENCES billing_order (id)');
        $this->addSql('CREATE INDEX order_id_idx ON billing_invoice (order_invoices_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE billing_billing_attribute DROP FOREIGN KEY FK_72E9EF7C17CC08DB');
        $this->addSql('DROP INDEX order_id_idx ON billing_billing_attribute');
        $this->addSql('ALTER TABLE billing_billing_attribute ADD order_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP order_attributes_id');
        $this->addSql('CREATE INDEX order_id_idx ON billing_billing_attribute (order_id)');
        $this->addSql('ALTER TABLE billing_invoice DROP FOREIGN KEY FK_FB4B9C933FE6C5F3');
        $this->addSql('DROP INDEX order_id_idx ON billing_invoice');
        $this->addSql('ALTER TABLE billing_invoice ADD order_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP order_invoices_id');
        $this->addSql('CREATE INDEX order_id_idx ON billing_invoice (order_id)');
    }
}
