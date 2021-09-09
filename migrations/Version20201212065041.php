<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201212065041 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX user_id_idx ON billing_billing_attribute');
        $this->addSql('ALTER TABLE billing_billing_attribute ADD seller_id VARCHAR(255) NOT NULL, CHANGE user_id buyer_id VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX user_id_idx ON billing_billing_attribute (id, seller_id, buyer_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX user_id_idx ON billing_billing_attribute');
        $this->addSql('ALTER TABLE billing_billing_attribute ADD user_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP buyer_id, DROP seller_id');
        $this->addSql('CREATE INDEX user_id_idx ON billing_billing_attribute (id, user_id)');
    }
}
