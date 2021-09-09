<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201207182536 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE billing_order DROP FOREIGN KEY FK_F056B6B5F65E9B0F');
        $this->addSql('ALTER TABLE billing_order ADD CONSTRAINT FK_F056B6B5F65E9B0F FOREIGN KEY (order_product_id) REFERENCES billing_product (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE billing_order DROP FOREIGN KEY FK_F056B6B5F65E9B0F');
        $this->addSql('ALTER TABLE billing_order ADD CONSTRAINT FK_F056B6B5F65E9B0F FOREIGN KEY (order_product_id) REFERENCES billing_order (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
