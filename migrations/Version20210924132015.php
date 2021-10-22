<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210924132015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE listing_review DROP CONSTRAINT fk_9dbba0157294869c');
        $this->addSql('DROP INDEX idx_9dbba0157294869c');
        $this->addSql('ALTER TABLE listing_review RENAME COLUMN article_id TO listing_id');
        $this->addSql('ALTER TABLE listing_review ADD CONSTRAINT FK_9DBBA015D4619D1A FOREIGN KEY (listing_id) REFERENCES listing (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9DBBA015D4619D1A ON listing_review (listing_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('ALTER TABLE listing_review DROP CONSTRAINT FK_9DBBA015D4619D1A');
        $this->addSql('DROP INDEX IDX_9DBBA015D4619D1A');
        $this->addSql('ALTER TABLE listing_review RENAME COLUMN listing_id TO article_id');
        $this->addSql('ALTER TABLE listing_review ADD CONSTRAINT fk_9dbba0157294869c FOREIGN KEY (article_id) REFERENCES listing (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_9dbba0157294869c ON listing_review (article_id)');
    }
}
