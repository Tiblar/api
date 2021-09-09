<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201218030620 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE billing_stripe_payment_method (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, stripe_payment_method_id VARCHAR(255) NOT NULL, brand VARCHAR(255) DEFAULT NULL, last_four VARCHAR(255) DEFAULT NULL, active TINYINT(1) NOT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (user_id, active), INDEX stripe_pm_idx (stripe_payment_method_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_stripe_customer (id VARCHAR(255) NOT NULL, default_payment_method_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) NOT NULL, stripe_customer_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, UNIQUE INDEX UNIQ_89F3B12DA76ED395 (user_id), UNIQUE INDEX UNIQ_89F3B12DAF212FD0 (default_payment_method_id), INDEX id_idx (id), INDEX user_id_idx (user_id), INDEX stripe_customer_idx (stripe_customer_id), INDEX default_payment_method_idx (default_payment_method_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE billing_stripe_customer ADD CONSTRAINT FK_89F3B12DAF212FD0 FOREIGN KEY (default_payment_method_id) REFERENCES billing_stripe_payment_method (id)');
        $this->addSql('ALTER TABLE billing_payment_method ADD stripe_payment_method VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE billing_payment_method ADD CONSTRAINT FK_F3A49941D72D9BCA FOREIGN KEY (stripe_payment_method) REFERENCES billing_stripe_payment_method (id)');
        $this->addSql('CREATE INDEX stripe_payment_method_idx ON billing_payment_method (stripe_payment_method)');
        $this->addSql('ALTER TABLE billing_product ADD stripe_product_id VARCHAR(255) DEFAULT NULL, ADD stripe_price_id VARCHAR(255) DEFAULT NULL, ADD stripe_price_annual_discount_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX stripe_product_id_idx ON billing_product (stripe_product_id)');
        $this->addSql('ALTER TABLE billing_product_attribute ADD stripe_price_id VARCHAR(255) DEFAULT NULL, ADD stripe_price_annual_discount_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE billing_payment_method DROP FOREIGN KEY FK_F3A49941D72D9BCA');
        $this->addSql('ALTER TABLE billing_stripe_customer DROP FOREIGN KEY FK_89F3B12DAF212FD0');
        $this->addSql('DROP TABLE billing_stripe_payment_method');
        $this->addSql('DROP TABLE billing_stripe_customer');
        $this->addSql('DROP INDEX stripe_payment_method_idx ON billing_payment_method');
        $this->addSql('ALTER TABLE billing_payment_method DROP stripe_payment_method');
        $this->addSql('DROP INDEX stripe_product_id_idx ON billing_product');
        $this->addSql('ALTER TABLE billing_product DROP stripe_product_id, DROP stripe_price_id, DROP stripe_price_annual_discount_id');
        $this->addSql('ALTER TABLE billing_product_attribute DROP stripe_price_id, DROP stripe_price_annual_discount_id');
    }
}
