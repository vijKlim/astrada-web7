<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211029121129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE listing_category_translation (id SERIAL NOT NULL, translatable_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_606EDC1F2C2AC5D3 ON listing_category_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX listing_category_translation_uniq_trans ON listing_category_translation (translatable_id, locale)');
        $this->addSql('ALTER TABLE listing_category_translation ADD CONSTRAINT FK_606EDC1F2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES listing_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('DROP TABLE listing_category_translation');
    }
}
