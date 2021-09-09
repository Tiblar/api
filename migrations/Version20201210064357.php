<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201210064357 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX order_id_idx ON billing_order');
        $this->addSql('ALTER TABLE billing_order CHANGE cancelled active TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX order_id_idx ON billing_order (id, recurring, active)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX order_id_idx ON billing_order');
        $this->addSql('ALTER TABLE billing_order CHANGE active cancelled TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX order_id_idx ON billing_order (id, recurring, cancelled)');
    }
}
