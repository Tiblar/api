<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201205191428 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE billing_payment_method (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, order_id VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, recurring TINYINT(1) NOT NULL, cancelled TINYINT(1) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (id, user_id), INDEX order_id_idx (order_id, recurring, cancelled), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_product (id VARCHAR(255) NOT NULL, product_user_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, price NUMERIC(7, 2) NOT NULL, subscription_duration JSON DEFAULT NULL, annual_discount INT DEFAULT NULL, user_limit INT NOT NULL, shipping TINYINT(1) NOT NULL, published TINYINT(1) NOT NULL, unpublished_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_B8648F7A76B7C825 (product_user_id), INDEX id_idx (id), INDEX user_idx (id, product_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_order (id VARCHAR(255) NOT NULL, order_product_id VARCHAR(255) DEFAULT NULL, buyer_id VARCHAR(255) NOT NULL, seller_id VARCHAR(255) NOT NULL, recurring TINYINT(1) NOT NULL, cancelled TINYINT(1) NOT NULL, expire_timestamp DATETIME DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_F056B6B5F65E9B0F (order_product_id), INDEX id_idx (id), INDEX user_id_idx (id, buyer_id, seller_id), INDEX order_id_idx (id, recurring, cancelled), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_billing_attribute (id VARCHAR(255) NOT NULL, product_attribute_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) NOT NULL, order_id VARCHAR(255) NOT NULL, quantity NUMERIC(10, 0) NOT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (id, user_id), INDEX order_id_idx (order_id), INDEX product_attribute_id_idx (product_attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_invoice (id VARCHAR(255) NOT NULL, payment_method_id VARCHAR(255) DEFAULT NULL, buyer_id VARCHAR(255) NOT NULL, seller_id VARCHAR(255) NOT NULL, order_id VARCHAR(255) NOT NULL, event VARCHAR(255) NOT NULL, payment_status VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, expire_timestamp DATETIME DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_FB4B9C935AA1164F (payment_method_id), INDEX id_idx (id), INDEX user_id_idx (id, buyer_id, seller_id), INDEX order_id_idx (order_id), INDEX status_idx (event, payment_status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_product_attribute (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, product_id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, price NUMERIC(7, 2) NOT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE billing_product ADD CONSTRAINT FK_B8648F7A76B7C825 FOREIGN KEY (product_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE billing_order ADD CONSTRAINT FK_F056B6B5F65E9B0F FOREIGN KEY (order_product_id) REFERENCES billing_order (id)');
        $this->addSql('ALTER TABLE billing_billing_attribute ADD CONSTRAINT FK_72E9EF7C3B420C91 FOREIGN KEY (product_attribute_id) REFERENCES billing_product_attribute (id)');
        $this->addSql('ALTER TABLE billing_invoice ADD CONSTRAINT FK_FB4B9C935AA1164F FOREIGN KEY (payment_method_id) REFERENCES billing_payment_method (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE billing_invoice DROP FOREIGN KEY FK_FB4B9C935AA1164F');
        $this->addSql('ALTER TABLE billing_order DROP FOREIGN KEY FK_F056B6B5F65E9B0F');
        $this->addSql('ALTER TABLE billing_billing_attribute DROP FOREIGN KEY FK_72E9EF7C3B420C91');
        $this->addSql('DROP TABLE billing_payment_method');
        $this->addSql('DROP TABLE billing_product');
        $this->addSql('DROP TABLE billing_order');
        $this->addSql('DROP TABLE billing_billing_attribute');
        $this->addSql('DROP TABLE billing_invoice');
        $this->addSql('DROP TABLE billing_product_attribute');
    }
}
