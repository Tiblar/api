<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200920211149 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        if(!$schema->hasTable("privacy") == true) {
            $this->addSql('CREATE TABLE privacy (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, view INT DEFAULT 0 NOT NULL, likes TINYINT(1) DEFAULT \'0\' NOT NULL, following TINYINT(1) DEFAULT \'1\' NOT NULL, follower_count TINYINT(1) DEFAULT \'1\' NOT NULL, asks TINYINT(1) DEFAULT \'1\' NOT NULL, reply TINYINT(1) DEFAULT \'1\' NOT NULL, message TINYINT(1) DEFAULT \'1\' NOT NULL, recommend TINYINT(1) DEFAULT \'1\' NOT NULL, INDEX privacy_idx (user_id, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("confirm_email") == true) {
            $this->addSql('CREATE TABLE confirm_email (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX confirm_email_user_id_idx (user_id), INDEX confirm_email_email_idx (email), INDEX confirm_email_code_idx (code), UNIQUE INDEX confirm_email_unique (user_id, email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("user_info") == true) {
            $this->addSql('CREATE TABLE user_info (id VARCHAR(255) NOT NULL, username VARCHAR(32) NOT NULL, username_color VARCHAR(255) DEFAULT NULL, join_date DATETIME NOT NULL, avatar VARCHAR(255) NOT NULL, banner VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, nsfw TINYINT(1) NOT NULL, biography LONGTEXT DEFAULT NULL, follower_count BIGINT NOT NULL, location VARCHAR(255) DEFAULT NULL, profile_theme VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B1087D9EF85E0677 (username), INDEX user_info_idx (id), INDEX user_username_idx (id, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("user") == true) {
            $this->addSql('CREATE TABLE user (id VARCHAR(255) NOT NULL, user_info_id VARCHAR(255) DEFAULT NULL, user_privacy_id VARCHAR(255) DEFAULT NULL, user_confirm_email_id VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, verified TINYINT(1) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json_array)\', boosted TINYINT(1) NOT NULL, storage_limit NUMERIC(16, 8) NOT NULL, storage NUMERIC(16, 8) NOT NULL, theme VARCHAR(255) NOT NULL, nsfw_filter TINYINT(1) NOT NULL, two_factor TINYINT(1) NOT NULL, two_factor_type VARCHAR(255) DEFAULT NULL, banned TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649586DFF2 (user_info_id), UNIQUE INDEX UNIQ_8D93D649BEE0BC86 (user_privacy_id), UNIQUE INDEX UNIQ_8D93D6492996CBC5 (user_confirm_email_id), INDEX user_idx (id), INDEX user_info_idx (id, user_info_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

            if(!$schema->hasTable("user") == true) {
                $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649586DFF2 FOREIGN KEY (user_info_id) REFERENCES user_info (id)');
                $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649BEE0BC86 FOREIGN KEY (user_privacy_id) REFERENCES privacy (id)');
                $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6492996CBC5 FOREIGN KEY (user_confirm_email_id) REFERENCES confirm_email (id)');
            }
        }

        if(!$schema->hasTable("poll") == true) {
            $this->addSql('CREATE TABLE poll (id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, question VARCHAR(255) NOT NULL, o1 VARCHAR(255) NOT NULL, o2 VARCHAR(255) NOT NULL, o3 VARCHAR(255) DEFAULT NULL, o4 VARCHAR(255) DEFAULT NULL, o1_votes_count INT NOT NULL, o2_votes_count INT NOT NULL, o3_votes_count INT NOT NULL, o4_votes_count INT NOT NULL, votes_count INT NOT NULL, expire_timestamp DATETIME NOT NULL, INDEX post_idx (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("magnet") == true) {
            $this->addSql('CREATE TABLE magnet (id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, magnet LONGTEXT NOT NULL, timestamp DATETIME NOT NULL, INDEX post_idx (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("post") == true) {
            $this->addSql('CREATE TABLE post (id VARCHAR(255) NOT NULL, post_user_id VARCHAR(255) DEFAULT NULL, reblogged_post_id VARCHAR(255) DEFAULT NULL, poll_id VARCHAR(255) DEFAULT NULL, post_magnet_id VARCHAR(255) DEFAULT NULL, old_uuid VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, body LONGTEXT DEFAULT NULL, favorites_count BIGINT DEFAULT NULL, reblogs_count BIGINT DEFAULT NULL, replies_count BIGINT DEFAULT NULL, nsfw TINYINT(1) DEFAULT \'0\' NOT NULL, private TINYINT(1) DEFAULT \'0\' NOT NULL, mark_delete TINYINT(1) DEFAULT \'0\' NOT NULL, timestamp DATETIME NOT NULL, updated_timestamp DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_5A8A6C8D3C947C0F (poll_id), UNIQUE INDEX UNIQ_5A8A6C8DA66A631A (post_magnet_id), INDEX post_idx (id), INDEX post_author_idx (post_user_id), INDEX post_favorites_count (favorites_count), INDEX post_reblog (reblogged_post_id), INDEX post_id_reblog (id, post_user_id, reblogged_post_id), INDEX post_author_reblog (post_user_id, reblogged_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

            if(!$schema->hasTable("post") == true) {
                $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D9A8664A6 FOREIGN KEY (post_user_id) REFERENCES user (id)');
                $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D16EB3E1D FOREIGN KEY (reblogged_post_id) REFERENCES post (id)');
                $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
                $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA66A631A FOREIGN KEY (post_magnet_id) REFERENCES magnet (id)');
            }
        }

        if(!$schema->hasTable("tag_list") == true) {
            $this->addSql('CREATE TABLE tag_list (id VARCHAR(255) NOT NULL, post VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX tag_list_title_idx (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("tag") == true) {
            $this->addSql('CREATE TABLE tag (id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, nsfw TINYINT(1) NOT NULL, count BIGINT DEFAULT 1 NOT NULL, INDEX tag_idx (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("reply") == true) {
            $this->addSql('CREATE TABLE reply (id VARCHAR(255) NOT NULL, reply_post_id VARCHAR(255) DEFAULT NULL, reply_user_id VARCHAR(255) DEFAULT NULL, reply_parent_id VARCHAR(255) DEFAULT NULL, body LONGTEXT NOT NULL, depth INT NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_FDA8C6E0BFD8BBFE (reply_parent_id), INDEX reply_post_idx (reply_post_id), INDEX reply_author (reply_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

            if(!$schema->hasTable("reply") == true) {
                $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E056376AEE FOREIGN KEY (reply_post_id) REFERENCES post (id)');
                $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E0BAD0BA57 FOREIGN KEY (reply_user_id) REFERENCES user (id)');
                $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E0BFD8BBFE FOREIGN KEY (reply_parent_id) REFERENCES reply (id)');
            }
        }

        if(!$schema->hasTable("post_user_mentions") == true) {
            $this->addSql('CREATE TABLE post_user_mentions (id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, reply_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) NOT NULL, causer_id VARCHAR(255) NOT NULL, indices JSON NOT NULL, timestamp DATETIME NOT NULL, INDEX post_idx (post_id), INDEX user_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("favorite") == true) {
            $this->addSql('CREATE TABLE favorite (id VARCHAR(255) NOT NULL, favoriter VARCHAR(255) NOT NULL, favorited VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX post_idx (post_id), INDEX post_favoriter_idx (post_id, favoriter), INDEX favoriter_idx (favoriter), INDEX favorited_idx (favorited), INDEX timestamp_idx (timestamp), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("two_factor_email_token") == true) {
            $this->addSql('CREATE TABLE two_factor_email_token (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX disable_2fa_user_id_idx (user_id), INDEX disable_2fa_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("user_service_connection") == true) {
            $this->addSql('CREATE TABLE user_service_connection (id BIGINT AUTO_INCREMENT NOT NULL, user_id VARCHAR(255) NOT NULL, service VARCHAR(255) NOT NULL, account VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, INDEX user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("user_password_reset_token") == true) {
            $this->addSql('CREATE TABLE user_password_reset_token (id BIGINT AUTO_INCREMENT NOT NULL, user_id VARCHAR(255) NOT NULL, service VARCHAR(255) NOT NULL, account VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, INDEX user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("pin") == true) {
            $this->addSql('CREATE TABLE pin (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX user_unique (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("follow") == true) {
            $this->addSql('CREATE TABLE follow (id VARCHAR(255) NOT NULL, follower_id VARCHAR(255) NOT NULL, followed_id VARCHAR(255) NOT NULL, timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX follow_follower_idx (follower_id), INDEX follow_followed_idx (followed_id), UNIQUE INDEX follow_unique (follower_id, followed_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("invite") == true) {
            $this->addSql('CREATE TABLE invite (id VARCHAR(255) NOT NULL, inviter VARCHAR(255) NOT NULL, invited VARCHAR(255) NOT NULL, complete TINYINT(1) NOT NULL, timestamp DATETIME NOT NULL, INDEX invite_inviter_idx (inviter, complete), INDEX invite_invited_idx (invited), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("block") == true) {
            $this->addSql('CREATE TABLE block (id VARCHAR(255) NOT NULL, blocker_id VARCHAR(255) NOT NULL, blocked_id VARCHAR(255) NOT NULL, timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX block_blocker_idx (blocker_id), INDEX block_blocked_idx (blocked_id), UNIQUE INDEX block_unique (blocker_id, blocked_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("follow_request") == true) {
            $this->addSql('CREATE TABLE follow_request (id VARCHAR(255) NOT NULL, requester_id VARCHAR(255) NOT NULL, requested_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX follow_request_requester_idx (requester_id), INDEX follow_request_requested_idx (requested_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("disable_2fa_email") == true) {
            $this->addSql('CREATE TABLE disable_2fa_email (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX disable_2fa_user_id_idx (user_id), INDEX disable_2fa_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("notification") == true) {
            $this->addSql('CREATE TABLE notification (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id BIGINT DEFAULT NULL, type VARCHAR(255) NOT NULL, interactions_count INT NOT NULL, seen TINYINT(1) NOT NULL, message VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX notification_idx (user_id), INDEX notification_date_idx (type, timestamp), INDEX notification_post_idx (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("notification_user") == true) {
            $this->addSql('CREATE TABLE notification_user (notification_id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, INDEX IDX_35AF9D73EF1A9D84 (notification_id), INDEX IDX_35AF9D73A76ED395 (user_id), PRIMARY KEY(notification_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

            if(!$schema->hasTable("notification_user") == true) {
                $this->addSql('ALTER TABLE notification_user ADD CONSTRAINT FK_35AF9D73EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON DELETE CASCADE');
                $this->addSql('ALTER TABLE notification_user ADD CONSTRAINT FK_35AF9D73A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
            }
        }


        if(!$schema->hasTable("user_action_log") == true) {
            $this->addSql('CREATE TABLE user_action_log (id VARCHAR(255) NOT NULL, action_log_user_id VARCHAR(255) DEFAULT NULL, action VARCHAR(255) NOT NULL, metadata JSON DEFAULT NULL, ip_address VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_15A23069E47162C6 (action_log_user_id), INDEX user_action_log_idx (action, action_log_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

            if(!$schema->hasTable("user_action_log") == true) {
                $this->addSql('ALTER TABLE user_action_log ADD CONSTRAINT FK_15A23069E47162C6 FOREIGN KEY (action_log_user_id) REFERENCES user (id)');
            }
        }

        if(!$schema->hasTable("jwt_refresh_token") == true) {
            $this->addSql('CREATE TABLE jwt_refresh_token (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, refresh_token VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, INDEX jwt_refresh_token_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("captcha") == true) {
            $this->addSql('CREATE TABLE captcha (id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, consumed TINYINT(1) NOT NULL, expire_timestamp DATETIME NOT NULL, INDEX captcha_solve_idx (id, code, consumed), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("staff_api_user") == true) {
            $this->addSql('CREATE TABLE staff_api_user (id VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json_array)\', INDEX user_idx (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("file") == true) {
            $this->addSql('CREATE TABLE file (id VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, file_size NUMERIC(16, 8) NOT NULL, hash VARCHAR(255) NOT NULL, hash_name VARCHAR(255) NOT NULL, height INT DEFAULT NULL, width INT DEFAULT NULL, INDEX id_idx (id), INDEX hash_idx (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("audio_wave") == true) {
            $this->addSql('CREATE TABLE audio_wave (id VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, data JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if(!$schema->hasTable("attachment") == true) {
            $this->addSql('CREATE TABLE attachment (id VARCHAR(255) NOT NULL, attachment_post_id VARCHAR(255) DEFAULT NULL, attachment_file_id VARCHAR(255) DEFAULT NULL, `row` INT DEFAULT 0 NOT NULL, original_name VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_795FD9BB831C56AA (attachment_post_id), INDEX IDX_795FD9BB5B5E2CEA (attachment_file_id), INDEX attachment_idx (id, attachment_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

            if(!$schema->hasTable('attachment')){
                $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BB831C56AA FOREIGN KEY (attachment_post_id) REFERENCES post (id)');
                $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BB5B5E2CEA FOREIGN KEY (attachment_file_id) REFERENCES file (id)');
            }
        }

        if(!$schema->hasTable("poll_vote") == true) {
            $this->addSql('CREATE TABLE poll_vote (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, choice INT NOT NULL, timestamp DATETIME NOT NULL, INDEX poll_vote_idx (post_id), INDEX poll_vote_user_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D16EB3E1D');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E056376AEE');
        $this->addSql('ALTER TABLE attachment DROP FOREIGN KEY FK_795FD9BB831C56AA');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E0BFD8BBFE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649BEE0BC86');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6492996CBC5');
        $this->addSql('ALTER TABLE notification_user DROP FOREIGN KEY FK_35AF9D73EF1A9D84');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D9A8664A6');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E0BAD0BA57');
        $this->addSql('ALTER TABLE notification_user DROP FOREIGN KEY FK_35AF9D73A76ED395');
        $this->addSql('ALTER TABLE user_action_log DROP FOREIGN KEY FK_15A23069E47162C6');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649586DFF2');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA66A631A');
        $this->addSql('ALTER TABLE attachment DROP FOREIGN KEY FK_795FD9BB5B5E2CEA');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D3C947C0F');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE tag_list');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE reply');
        $this->addSql('DROP TABLE post_user_mentions');
        $this->addSql('DROP TABLE favorite');
        $this->addSql('DROP TABLE two_factor_email_token');
        $this->addSql('DROP TABLE user_service_connection');
        $this->addSql('DROP TABLE user_password_reset_token');
        $this->addSql('DROP TABLE pin');
        $this->addSql('DROP TABLE follow');
        $this->addSql('DROP TABLE invite');
        $this->addSql('DROP TABLE privacy');
        $this->addSql('DROP TABLE block');
        $this->addSql('DROP TABLE follow_request');
        $this->addSql('DROP TABLE disable_2fa_email');
        $this->addSql('DROP TABLE confirm_email');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE notification_user');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_action_log');
        $this->addSql('DROP TABLE jwt_refresh_token');
        $this->addSql('DROP TABLE user_info');
        $this->addSql('DROP TABLE captcha');
        $this->addSql('DROP TABLE staff_api_user');
        $this->addSql('DROP TABLE magnet');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE audio_wave');
        $this->addSql('DROP TABLE poll');
        $this->addSql('DROP TABLE attachment');
        $this->addSql('DROP TABLE poll_vote');
    }
}
