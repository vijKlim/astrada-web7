<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211004130510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking (id SERIAL NOT NULL, topic_id INT DEFAULT NULL, user_id INT DEFAULT NULL, listing_id INT DEFAULT NULL, status SMALLINT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, "end" TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, start_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, end_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, validated BOOLEAN DEFAULT \'false\' NOT NULL, new_booking_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, canceled_asker_booking_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, alerted_expiring BOOLEAN DEFAULT \'false\' NOT NULL, message TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E00CEDDE1F55203D ON booking (topic_id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA76ED395 ON booking (user_id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDED4619D1A ON booking (listing_id)');
        $this->addSql('CREATE TABLE post (id SERIAL NOT NULL, topic_id INT DEFAULT NULL, booking_id INT DEFAULT NULL, reply_to_id INT DEFAULT NULL, author_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, body TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8D77153098 ON post (code)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D1F55203D ON post (topic_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D3301C60 ON post (booking_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DFFDF7169 ON post (reply_to_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DF675F31B ON post (author_id)');
        $this->addSql('CREATE TABLE topic (id SERIAL NOT NULL, main_post_id INT DEFAULT NULL, author_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, post_count INT NOT NULL, view_count INT NOT NULL, last_post_created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D40DE1B77153098 ON topic (code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D40DE1BBFC2AAC5 ON topic (main_post_id)');
        $this->addSql('CREATE INDEX IDX_9D40DE1BF675F31B ON topic (author_id)');
        $this->addSql('CREATE TABLE topic_followers (topic_id INT NOT NULL, customerinterface_id INT NOT NULL, PRIMARY KEY(topic_id, customerinterface_id))');
        $this->addSql('CREATE INDEX IDX_6D9D691F1F55203D ON topic_followers (topic_id)');
        $this->addSql('CREATE INDEX IDX_6D9D691FF6FD7074 ON topic_followers (customerinterface_id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES api_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDED4619D1A FOREIGN KEY (listing_id) REFERENCES listing (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D3301C60 FOREIGN KEY (booking_id) REFERENCES booking (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DFFDF7169 FOREIGN KEY (reply_to_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES sylius_customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1BBFC2AAC5 FOREIGN KEY (main_post_id) REFERENCES post (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1BF675F31B FOREIGN KEY (author_id) REFERENCES sylius_customer (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE topic_followers ADD CONSTRAINT FK_6D9D691F1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE topic_followers ADD CONSTRAINT FK_6D9D691FF6FD7074 FOREIGN KEY (customerinterface_id) REFERENCES sylius_customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D3301C60');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DFFDF7169');
        $this->addSql('ALTER TABLE topic DROP CONSTRAINT FK_9D40DE1BBFC2AAC5');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT FK_E00CEDDE1F55203D');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D1F55203D');
        $this->addSql('ALTER TABLE topic_followers DROP CONSTRAINT FK_6D9D691F1F55203D');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE topic');
        $this->addSql('DROP TABLE topic_followers');
    }
}
