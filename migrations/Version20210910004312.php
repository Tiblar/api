<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210910004312 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE analytics_post (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, views BIGINT NOT NULL, source VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX analytics_idx (user_id, post_id, timestamp), INDEX analytics_post_idx (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE analytics_user (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, views BIGINT NOT NULL, timestamp DATETIME NOT NULL, INDEX analytics_idx (user_id, timestamp), INDEX analytics_user_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE analytics_view_log (id VARCHAR(255) NOT NULL, resource_id BIGINT NOT NULL, user_id VARCHAR(255) NOT NULL, source VARCHAR(255) NOT NULL, ip_address VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX log_ip_address_idx (ip_address, timestamp), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE application (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, client_secret VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX application_user_id_idx (user_id), INDEX application_client_id_idx (id, client_secret), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attachment (id VARCHAR(255) NOT NULL, attachment_post_id VARCHAR(255) DEFAULT NULL, attachment_file_id VARCHAR(255) DEFAULT NULL, `row` INT DEFAULT 0 NOT NULL, original_name VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_795FD9BB831C56AA (attachment_post_id), INDEX IDX_795FD9BB5B5E2CEA (attachment_file_id), INDEX attachment_idx (id, attachment_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audio_wave (id VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, data JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_billing_attribute (id VARCHAR(255) NOT NULL, order_attributes_id VARCHAR(255) DEFAULT NULL, product_attribute_id VARCHAR(255) DEFAULT NULL, buyer_id VARCHAR(255) NOT NULL, seller_id VARCHAR(255) NOT NULL, quantity NUMERIC(10, 0) NOT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (id, seller_id, buyer_id), INDEX order_id_idx (order_attributes_id), INDEX product_attribute_id_idx (product_attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_crypto_payment_method (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, dest_tag VARCHAR(255) DEFAULT NULL, amount NUMERIC(16, 8) NOT NULL, confirmations INT NOT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (user_id), INDEX address_idx (address, dest_tag), INDEX type_idx (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_invoice (id VARCHAR(255) NOT NULL, order_invoices_id VARCHAR(255) DEFAULT NULL, payment_method_id VARCHAR(255) DEFAULT NULL, tx_id VARCHAR(255) DEFAULT NULL, buyer_id VARCHAR(255) NOT NULL, seller_id VARCHAR(255) NOT NULL, event VARCHAR(255) NOT NULL, payment_status VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, expire_timestamp DATETIME DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_FB4B9C935AA1164F (payment_method_id), INDEX id_idx (id), INDEX user_id_idx (id, buyer_id, seller_id), INDEX order_id_idx (order_invoices_id), INDEX status_idx (event, payment_status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_order (id VARCHAR(255) NOT NULL, order_product_id VARCHAR(255) DEFAULT NULL, buyer_id VARCHAR(255) NOT NULL, seller_id VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, frequency VARCHAR(255) DEFAULT NULL, recurring TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, stripe_subscription_id VARCHAR(255) DEFAULT NULL, expire_timestamp DATETIME DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_F056B6B5F65E9B0F (order_product_id), INDEX id_idx (id), INDEX user_id_idx (id, buyer_id, seller_id), INDEX order_id_idx (id, recurring, active), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_payment_method (id VARCHAR(255) NOT NULL, crypto_payment_method VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) NOT NULL, order_id VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, recurring TINYINT(1) NOT NULL, cancelled TINYINT(1) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (id, user_id), INDEX order_id_idx (order_id, recurring, cancelled), INDEX crypto_payment_method_idx (crypto_payment_method), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_product (id VARCHAR(255) NOT NULL, product_user_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, price NUMERIC(7, 2) NOT NULL, subscription_frequency JSON DEFAULT NULL, annual_discount INT DEFAULT NULL, user_limit INT DEFAULT NULL, shipping TINYINT(1) NOT NULL, stripe_product_id VARCHAR(255) DEFAULT NULL, stripe_price_id VARCHAR(255) DEFAULT NULL, stripe_price_annual_discount_id VARCHAR(255) DEFAULT NULL, published TINYINT(1) NOT NULL, unpublished_timestamp DATETIME DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_B8648F7A76B7C825 (product_user_id), INDEX id_idx (id), INDEX user_idx (id, product_user_id), INDEX stripe_product_id_idx (stripe_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_product_attribute (id VARCHAR(255) NOT NULL, product_attribute_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) NOT NULL, min_quantity INT NOT NULL, max_quantity INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, price NUMERIC(7, 2) NOT NULL, stripe_price_id VARCHAR(255) DEFAULT NULL, stripe_price_annual_discount_id VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_A85A3BAF3B420C91 (product_attribute_id), INDEX id_idx (id), INDEX user_id_idx (id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_stripe_customer (id VARCHAR(255) NOT NULL, default_payment_method_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) NOT NULL, stripe_customer_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, UNIQUE INDEX UNIQ_89F3B12DA76ED395 (user_id), UNIQUE INDEX UNIQ_89F3B12DAF212FD0 (default_payment_method_id), INDEX id_idx (id), INDEX user_id_idx (user_id), INDEX stripe_customer_idx (stripe_customer_id), INDEX default_payment_method_idx (default_payment_method_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billing_stripe_payment_method (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, stripe_payment_method_id VARCHAR(255) NOT NULL, brand VARCHAR(255) DEFAULT NULL, last_four VARCHAR(255) DEFAULT NULL, active TINYINT(1) NOT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (user_id, active), INDEX stripe_pm_idx (stripe_payment_method_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE block (id VARCHAR(255) NOT NULL, blocker_id VARCHAR(255) NOT NULL, blocked_id VARCHAR(255) NOT NULL, timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX block_blocker_idx (blocker_id), INDEX block_blocked_idx (blocked_id), UNIQUE INDEX block_unique (blocker_id, blocked_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE captcha (id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, consumed TINYINT(1) NOT NULL, expire_timestamp DATETIME NOT NULL, INDEX captcha_solve_idx (id, code, consumed), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE confirm_email (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX confirm_email_user_id_idx (user_id), INDEX confirm_email_email_idx (email), INDEX confirm_email_code_idx (code), UNIQUE INDEX confirm_email_unique (user_id, email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE disable_2fa_email (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX disable_2fa_user_id_idx (user_id), INDEX disable_2fa_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favorite (id VARCHAR(255) NOT NULL, favoriter VARCHAR(255) NOT NULL, favorited VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX post_idx (post_id), INDEX post_favoriter_idx (post_id, favoriter), INDEX favoriter_idx (favoriter), INDEX favorited_idx (favorited), INDEX timestamp_idx (timestamp), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, file_size NUMERIC(16, 8) NOT NULL, hash VARCHAR(255) NOT NULL, hash_name VARCHAR(255) NOT NULL, extension VARCHAR(255) NOT NULL, height INT DEFAULT NULL, width INT DEFAULT NULL, duration INT DEFAULT NULL, INDEX id_idx (id), INDEX hash_idx (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_init (id VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, part_count INT NOT NULL, back_blaze_file_id VARCHAR(255) NOT NULL, file_size NUMERIC(16, 8) NOT NULL, max_file_size NUMERIC(16, 8) NOT NULL, hash VARCHAR(255) NOT NULL, extension VARCHAR(255) NOT NULL, original_name VARCHAR(255) DEFAULT NULL, file_id VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX backblaze_file_id_idx (back_blaze_file_id), INDEX user_id_idx (user_id, status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_part (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, file_init_id VARCHAR(255) NOT NULL, part INT NOT NULL, part_size NUMERIC(16, 8) NOT NULL, hash VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX id_idx (id), INDEX user_id_idx (user_id), UNIQUE INDEX file_part_unique (file_init_id, part), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE follow (id VARCHAR(255) NOT NULL, follower_id VARCHAR(255) NOT NULL, followed_id VARCHAR(255) NOT NULL, timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX follow_follower_idx (follower_id), INDEX follow_followed_idx (followed_id), UNIQUE INDEX follow_unique (follower_id, followed_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE follow_request (id VARCHAR(255) NOT NULL, requester_id VARCHAR(255) NOT NULL, requested_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX follow_request_requester_idx (requester_id), INDEX follow_request_requested_idx (requested_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invite (id VARCHAR(255) NOT NULL, inviter VARCHAR(255) NOT NULL, invited VARCHAR(255) NOT NULL, complete TINYINT(1) NOT NULL, timestamp DATETIME NOT NULL, INDEX invite_inviter_idx (inviter, complete), INDEX invite_invited_idx (invited), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jwt_refresh_token (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, refresh_token VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, INDEX jwt_refresh_token_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE magnet (id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, magnet LONGTEXT NOT NULL, timestamp DATETIME NOT NULL, INDEX post_idx (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id BIGINT DEFAULT NULL, type VARCHAR(255) NOT NULL, interactions_count INT NOT NULL, seen TINYINT(1) NOT NULL, message VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX notification_idx (user_id), INDEX notification_date_idx (type, timestamp), INDEX notification_post_idx (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification_user (notification_id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, INDEX IDX_35AF9D73EF1A9D84 (notification_id), INDEX IDX_35AF9D73A76ED395 (user_id), PRIMARY KEY(notification_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_access_token (id VARCHAR(255) NOT NULL, client_id VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, scopes JSON NOT NULL, user_id VARCHAR(255) NOT NULL, revoked TINYINT(1) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX oauth_access_token_idx (token, client_id, revoked), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_code (id VARCHAR(255) NOT NULL, client_id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, state VARCHAR(255) DEFAULT NULL, scopes JSON NOT NULL, user_id VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX oauth_code_idx (code, client_id, state), INDEX oauth_user_id_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_redirect_url (id VARCHAR(255) NOT NULL, client_id VARCHAR(255) DEFAULT NULL, url VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_D623665B19EB6921 (client_id), INDEX redirect_url_idx (client_id, url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_refresh_token (id VARCHAR(255) NOT NULL, access_token_id VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX oauth_refresh_token_idx (access_token_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pin (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX user_unique (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE poll (id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, question VARCHAR(255) NOT NULL, o1 VARCHAR(255) NOT NULL, o2 VARCHAR(255) NOT NULL, o3 VARCHAR(255) DEFAULT NULL, o4 VARCHAR(255) DEFAULT NULL, o1_votes_count INT NOT NULL, o2_votes_count INT NOT NULL, o3_votes_count INT NOT NULL, o4_votes_count INT NOT NULL, votes_count INT NOT NULL, expire_timestamp DATETIME NOT NULL, INDEX post_idx (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE poll_vote (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, choice INT NOT NULL, timestamp DATETIME NOT NULL, INDEX poll_vote_idx (post_id), INDEX poll_vote_user_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id VARCHAR(255) NOT NULL, post_user_id VARCHAR(255) DEFAULT NULL, reblogged_post_id VARCHAR(255) DEFAULT NULL, poll_id VARCHAR(255) DEFAULT NULL, post_magnet_id VARCHAR(255) DEFAULT NULL, old_uuid VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, body LONGTEXT DEFAULT NULL, favorites_count BIGINT DEFAULT NULL, reblogs_count BIGINT DEFAULT NULL, replies_count BIGINT DEFAULT NULL, nsfw TINYINT(1) DEFAULT \'0\' NOT NULL, private TINYINT(1) DEFAULT \'0\' NOT NULL, followers_only TINYINT(1) DEFAULT \'0\' NOT NULL, mark_delete TINYINT(1) DEFAULT \'0\' NOT NULL, hidden TINYINT(1) DEFAULT \'0\' NOT NULL, spam TINYINT(1) DEFAULT \'0\' NOT NULL, ip_address VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, updated_timestamp DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_5A8A6C8D3C947C0F (poll_id), UNIQUE INDEX UNIQ_5A8A6C8DA66A631A (post_magnet_id), INDEX post_idx (id), INDEX post_author_idx (post_user_id), INDEX post_favorites_count (favorites_count), INDEX post_reblog (reblogged_post_id), INDEX post_id_reblog (id, post_user_id, reblogged_post_id), INDEX post_author_reblog (post_user_id, reblogged_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_category (post_id VARCHAR(255) NOT NULL, category_id VARCHAR(255) NOT NULL, INDEX IDX_B9A190604B89032C (post_id), INDEX IDX_B9A1906012469DE2 (category_id), PRIMARY KEY(post_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_list (id VARCHAR(255) NOT NULL, list_user_id VARCHAR(255) DEFAULT NULL, title VARCHAR(50) NOT NULL, description VARCHAR(400) DEFAULT NULL, visibility VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_8D9D9F137B210E98 (list_user_id), INDEX post_list_idx (list_user_id, visibility), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_list_item (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, list_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX post_list_item_idx (id, user_id), INDEX post_list_item_post_idx (post_id), INDEX post_list_item_user_idx (id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_report (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, accepted TINYINT(1) NOT NULL, rejected TINYINT(1) NOT NULL, timestamp DATETIME NOT NULL, INDEX post_id (post_id), INDEX user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_user_mentions (id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, reply_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) NOT NULL, causer_id VARCHAR(255) NOT NULL, indices JSON NOT NULL, timestamp DATETIME NOT NULL, INDEX post_idx (post_id), INDEX user_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE privacy (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, view INT DEFAULT 0 NOT NULL, likes TINYINT(1) DEFAULT \'0\' NOT NULL, following TINYINT(1) DEFAULT \'1\' NOT NULL, follower_count TINYINT(1) DEFAULT \'1\' NOT NULL, asks TINYINT(1) DEFAULT \'1\' NOT NULL, reply TINYINT(1) DEFAULT \'1\' NOT NULL, message TINYINT(1) DEFAULT \'1\' NOT NULL, recommend TINYINT(1) DEFAULT \'1\' NOT NULL, video_history TINYINT(1) DEFAULT \'1\' NOT NULL, INDEX privacy_idx (user_id, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reply (id VARCHAR(255) NOT NULL, reply_post_id VARCHAR(255) DEFAULT NULL, reply_user_id VARCHAR(255) DEFAULT NULL, reply_parent_id VARCHAR(255) DEFAULT NULL, body LONGTEXT NOT NULL, depth INT NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_FDA8C6E0BFD8BBFE (reply_parent_id), INDEX reply_post_idx (reply_post_id), INDEX reply_author (reply_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spam_filter_ip_list (id VARCHAR(255) NOT NULL, ip_address VARCHAR(255) NOT NULL, rating NUMERIC(10, 0) NOT NULL, updated_timestamp DATETIME DEFAULT NULL, INDEX ip_idx (ip_address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spam_filter_word_list (id VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, count_ham INT NOT NULL, count_spam INT NOT NULL, INDEX token_idx (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE staff_api_user (id VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json_array)\', INDEX user_idx (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, nsfw TINYINT(1) NOT NULL, count BIGINT DEFAULT 1 NOT NULL, INDEX tag_idx (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_list (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX tag_list_idx (post), INDEX tag_list_user_id_idx (user_id), INDEX tag_list_tag_idx (tag), INDEX tag_list_title_idx (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE thumbnail (id VARCHAR(255) NOT NULL, thumbanil_attachment_id VARCHAR(255) DEFAULT NULL, attachment_file_id VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX IDX_C35726E6F9677BF (thumbanil_attachment_id), INDEX IDX_C35726E65B5E2CEA (attachment_file_id), INDEX thumbnail_idx (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE two_factor_email_token (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX disable_2fa_user_id_idx (user_id), INDEX disable_2fa_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id VARCHAR(255) NOT NULL, user_info_id VARCHAR(255) DEFAULT NULL, user_privacy_id VARCHAR(255) DEFAULT NULL, user_confirm_email_id VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, verified TINYINT(1) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json_array)\', boosted TINYINT(1) NOT NULL, storage_limit NUMERIC(16, 8) NOT NULL, storage NUMERIC(16, 8) NOT NULL, theme VARCHAR(255) NOT NULL, nsfw_filter TINYINT(1) NOT NULL, two_factor TINYINT(1) NOT NULL, two_factor_type VARCHAR(255) DEFAULT NULL, banned TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649586DFF2 (user_info_id), UNIQUE INDEX UNIQ_8D93D649BEE0BC86 (user_privacy_id), UNIQUE INDEX UNIQ_8D93D6492996CBC5 (user_confirm_email_id), INDEX user_idx (id), INDEX user_info_idx (id, user_info_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_action_log (id VARCHAR(255) NOT NULL, action_log_user_id VARCHAR(255) DEFAULT NULL, action VARCHAR(255) NOT NULL, metadata JSON DEFAULT NULL, ip_address VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_15A23069E47162C6 (action_log_user_id), INDEX user_action_log_idx (action, action_log_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_info (id VARCHAR(255) NOT NULL, username VARCHAR(32) NOT NULL, username_color VARCHAR(255) DEFAULT NULL, join_date DATETIME NOT NULL, avatar VARCHAR(255) NOT NULL, banner VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, nsfw TINYINT(1) NOT NULL, biography LONGTEXT DEFAULT NULL, follower_count BIGINT NOT NULL, location VARCHAR(255) DEFAULT NULL, profile_theme VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B1087D9EF85E0677 (username), INDEX user_info_idx (id), INDEX user_username_idx (id, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_password_reset_token (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, expire_timestamp DATETIME NOT NULL, timestamp DATETIME NOT NULL, INDEX user_password_reset_user_id_idx (user_id), INDEX user_password_reset_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_service_connection (id BIGINT AUTO_INCREMENT NOT NULL, user_id VARCHAR(255) NOT NULL, service VARCHAR(255) NOT NULL, account VARCHAR(255) NOT NULL, link VARCHAR(255) DEFAULT NULL, INDEX user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_category (id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_AECE2B7D2B36786B (title), INDEX category_idx (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_history (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, post_id VARCHAR(255) NOT NULL, last_id VARCHAR(255) DEFAULT NULL, timestamp DATETIME NOT NULL, INDEX history_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BB831C56AA FOREIGN KEY (attachment_post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BB5B5E2CEA FOREIGN KEY (attachment_file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE billing_billing_attribute ADD CONSTRAINT FK_72E9EF7C17CC08DB FOREIGN KEY (order_attributes_id) REFERENCES billing_order (id)');
        $this->addSql('ALTER TABLE billing_billing_attribute ADD CONSTRAINT FK_72E9EF7C3B420C91 FOREIGN KEY (product_attribute_id) REFERENCES billing_product_attribute (id)');
        $this->addSql('ALTER TABLE billing_invoice ADD CONSTRAINT FK_FB4B9C933FE6C5F3 FOREIGN KEY (order_invoices_id) REFERENCES billing_order (id)');
        $this->addSql('ALTER TABLE billing_invoice ADD CONSTRAINT FK_FB4B9C935AA1164F FOREIGN KEY (payment_method_id) REFERENCES billing_payment_method (id)');
        $this->addSql('ALTER TABLE billing_order ADD CONSTRAINT FK_F056B6B5F65E9B0F FOREIGN KEY (order_product_id) REFERENCES billing_product (id)');
        $this->addSql('ALTER TABLE billing_payment_method ADD CONSTRAINT FK_F3A49941E30656C9 FOREIGN KEY (crypto_payment_method) REFERENCES billing_crypto_payment_method (id)');
        $this->addSql('ALTER TABLE billing_product ADD CONSTRAINT FK_B8648F7A76B7C825 FOREIGN KEY (product_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE billing_product_attribute ADD CONSTRAINT FK_A85A3BAF3B420C91 FOREIGN KEY (product_attribute_id) REFERENCES billing_product (id)');
        $this->addSql('ALTER TABLE billing_stripe_customer ADD CONSTRAINT FK_89F3B12DAF212FD0 FOREIGN KEY (default_payment_method_id) REFERENCES billing_stripe_payment_method (id)');
        $this->addSql('ALTER TABLE notification_user ADD CONSTRAINT FK_35AF9D73EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification_user ADD CONSTRAINT FK_35AF9D73A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_redirect_url ADD CONSTRAINT FK_D623665B19EB6921 FOREIGN KEY (client_id) REFERENCES application (id)');
        $this->addSql('ALTER TABLE oauth_refresh_token ADD CONSTRAINT FK_55DCF7552CCB2688 FOREIGN KEY (access_token_id) REFERENCES oauth_access_token (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D9A8664A6 FOREIGN KEY (post_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D16EB3E1D FOREIGN KEY (reblogged_post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA66A631A FOREIGN KEY (post_magnet_id) REFERENCES magnet (id)');
        $this->addSql('ALTER TABLE post_category ADD CONSTRAINT FK_B9A190604B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_category ADD CONSTRAINT FK_B9A1906012469DE2 FOREIGN KEY (category_id) REFERENCES video_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_list ADD CONSTRAINT FK_8D9D9F137B210E98 FOREIGN KEY (list_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E056376AEE FOREIGN KEY (reply_post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E0BAD0BA57 FOREIGN KEY (reply_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E0BFD8BBFE FOREIGN KEY (reply_parent_id) REFERENCES reply (id)');
        $this->addSql('ALTER TABLE thumbnail ADD CONSTRAINT FK_C35726E6F9677BF FOREIGN KEY (thumbanil_attachment_id) REFERENCES attachment (id)');
        $this->addSql('ALTER TABLE thumbnail ADD CONSTRAINT FK_C35726E65B5E2CEA FOREIGN KEY (attachment_file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649586DFF2 FOREIGN KEY (user_info_id) REFERENCES user_info (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649BEE0BC86 FOREIGN KEY (user_privacy_id) REFERENCES privacy (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6492996CBC5 FOREIGN KEY (user_confirm_email_id) REFERENCES confirm_email (id)');
        $this->addSql('ALTER TABLE user_action_log ADD CONSTRAINT FK_15A23069E47162C6 FOREIGN KEY (action_log_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oauth_redirect_url DROP FOREIGN KEY FK_D623665B19EB6921');
        $this->addSql('ALTER TABLE thumbnail DROP FOREIGN KEY FK_C35726E6F9677BF');
        $this->addSql('ALTER TABLE billing_payment_method DROP FOREIGN KEY FK_F3A49941E30656C9');
        $this->addSql('ALTER TABLE billing_billing_attribute DROP FOREIGN KEY FK_72E9EF7C17CC08DB');
        $this->addSql('ALTER TABLE billing_invoice DROP FOREIGN KEY FK_FB4B9C933FE6C5F3');
        $this->addSql('ALTER TABLE billing_invoice DROP FOREIGN KEY FK_FB4B9C935AA1164F');
        $this->addSql('ALTER TABLE billing_order DROP FOREIGN KEY FK_F056B6B5F65E9B0F');
        $this->addSql('ALTER TABLE billing_product_attribute DROP FOREIGN KEY FK_A85A3BAF3B420C91');
        $this->addSql('ALTER TABLE billing_billing_attribute DROP FOREIGN KEY FK_72E9EF7C3B420C91');
        $this->addSql('ALTER TABLE billing_stripe_customer DROP FOREIGN KEY FK_89F3B12DAF212FD0');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6492996CBC5');
        $this->addSql('ALTER TABLE attachment DROP FOREIGN KEY FK_795FD9BB5B5E2CEA');
        $this->addSql('ALTER TABLE thumbnail DROP FOREIGN KEY FK_C35726E65B5E2CEA');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA66A631A');
        $this->addSql('ALTER TABLE notification_user DROP FOREIGN KEY FK_35AF9D73EF1A9D84');
        $this->addSql('ALTER TABLE oauth_refresh_token DROP FOREIGN KEY FK_55DCF7552CCB2688');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D3C947C0F');
        $this->addSql('ALTER TABLE attachment DROP FOREIGN KEY FK_795FD9BB831C56AA');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D16EB3E1D');
        $this->addSql('ALTER TABLE post_category DROP FOREIGN KEY FK_B9A190604B89032C');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E056376AEE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649BEE0BC86');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E0BFD8BBFE');
        $this->addSql('ALTER TABLE billing_product DROP FOREIGN KEY FK_B8648F7A76B7C825');
        $this->addSql('ALTER TABLE notification_user DROP FOREIGN KEY FK_35AF9D73A76ED395');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D9A8664A6');
        $this->addSql('ALTER TABLE post_list DROP FOREIGN KEY FK_8D9D9F137B210E98');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E0BAD0BA57');
        $this->addSql('ALTER TABLE user_action_log DROP FOREIGN KEY FK_15A23069E47162C6');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649586DFF2');
        $this->addSql('ALTER TABLE post_category DROP FOREIGN KEY FK_B9A1906012469DE2');
        $this->addSql('DROP TABLE analytics_post');
        $this->addSql('DROP TABLE analytics_user');
        $this->addSql('DROP TABLE analytics_view_log');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE attachment');
        $this->addSql('DROP TABLE audio_wave');
        $this->addSql('DROP TABLE billing_billing_attribute');
        $this->addSql('DROP TABLE billing_crypto_payment_method');
        $this->addSql('DROP TABLE billing_invoice');
        $this->addSql('DROP TABLE billing_order');
        $this->addSql('DROP TABLE billing_payment_method');
        $this->addSql('DROP TABLE billing_product');
        $this->addSql('DROP TABLE billing_product_attribute');
        $this->addSql('DROP TABLE billing_stripe_customer');
        $this->addSql('DROP TABLE billing_stripe_payment_method');
        $this->addSql('DROP TABLE block');
        $this->addSql('DROP TABLE captcha');
        $this->addSql('DROP TABLE confirm_email');
        $this->addSql('DROP TABLE disable_2fa_email');
        $this->addSql('DROP TABLE favorite');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE file_init');
        $this->addSql('DROP TABLE file_part');
        $this->addSql('DROP TABLE follow');
        $this->addSql('DROP TABLE follow_request');
        $this->addSql('DROP TABLE invite');
        $this->addSql('DROP TABLE jwt_refresh_token');
        $this->addSql('DROP TABLE magnet');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE notification_user');
        $this->addSql('DROP TABLE oauth_access_token');
        $this->addSql('DROP TABLE oauth_code');
        $this->addSql('DROP TABLE oauth_redirect_url');
        $this->addSql('DROP TABLE oauth_refresh_token');
        $this->addSql('DROP TABLE pin');
        $this->addSql('DROP TABLE poll');
        $this->addSql('DROP TABLE poll_vote');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_category');
        $this->addSql('DROP TABLE post_list');
        $this->addSql('DROP TABLE post_list_item');
        $this->addSql('DROP TABLE post_report');
        $this->addSql('DROP TABLE post_user_mentions');
        $this->addSql('DROP TABLE privacy');
        $this->addSql('DROP TABLE reply');
        $this->addSql('DROP TABLE spam_filter_ip_list');
        $this->addSql('DROP TABLE spam_filter_word_list');
        $this->addSql('DROP TABLE staff_api_user');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_list');
        $this->addSql('DROP TABLE thumbnail');
        $this->addSql('DROP TABLE two_factor_email_token');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_action_log');
        $this->addSql('DROP TABLE user_info');
        $this->addSql('DROP TABLE user_password_reset_token');
        $this->addSql('DROP TABLE user_service_connection');
        $this->addSql('DROP TABLE video_category');
        $this->addSql('DROP TABLE video_history');
    }
}
