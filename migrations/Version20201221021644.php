<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201221021644 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE billing_crypto_payment_method (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, dest_tag VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (user_id), INDEX address_idx (address, dest_tag), INDEX type_idx (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE billing_payment_method DROP FOREIGN KEY FK_F3A49941D72D9BCA');
        $this->addSql('DROP INDEX stripe_payment_method_idx ON billing_payment_method');
        $this->addSql('ALTER TABLE billing_payment_method CHANGE stripe_payment_method crypto_payment_method VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE billing_payment_method ADD CONSTRAINT FK_F3A49941E30656C9 FOREIGN KEY (crypto_payment_method) REFERENCES billing_crypto_payment_method (id)');
        $this->addSql('CREATE INDEX crypto_payment_method_idx ON billing_payment_method (crypto_payment_method)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE billing_payment_method DROP FOREIGN KEY FK_F3A49941E30656C9');
        $this->addSql('DROP TABLE billing_crypto_payment_method');
        $this->addSql('DROP INDEX crypto_payment_method_idx ON billing_payment_method');
        $this->addSql('ALTER TABLE billing_payment_method CHANGE crypto_payment_method stripe_payment_method VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE billing_payment_method ADD CONSTRAINT FK_F3A49941D72D9BCA FOREIGN KEY (stripe_payment_method) REFERENCES billing_stripe_payment_method (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX stripe_payment_method_idx ON billing_payment_method (stripe_payment_method)');
    }
}
