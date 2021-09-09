<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210818235637 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag_list ADD user_id VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX tag_list_user_id_idx ON tag_list (user_id)');
        $this->addSql('UPDATE tag_list t SET user_id = (SELECT p.post_user_id FROM post p where p.id = t.post)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX tag_list_user_id_idx ON tag_list');
        $this->addSql('ALTER TABLE tag_list DROP user_id');
    }
}
