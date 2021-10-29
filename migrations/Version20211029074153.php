<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211029074153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE listing_attribute (id SERIAL NOT NULL, code VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, storage_type VARCHAR(255) NOT NULL, configuration TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, position INT NOT NULL, translatable BOOLEAN DEFAULT \'true\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A4C0BBB77153098 ON listing_attribute (code)');
        $this->addSql('COMMENT ON COLUMN listing_attribute.configuration IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE listing_attribute_category (id SERIAL NOT NULL, attribute_id INT NOT NULL, category_id INT NOT NULL, position INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_92BF28B0B6E62EFA ON listing_attribute_category (attribute_id)');
        $this->addSql('CREATE INDEX IDX_92BF28B012469DE2 ON listing_attribute_category (category_id)');
        $this->addSql('CREATE UNIQUE INDEX listing_attribute_category_unique ON listing_attribute_category (attribute_id, category_id)');
        $this->addSql('CREATE TABLE listing_category (id SERIAL NOT NULL, tree_root INT DEFAULT NULL, parent_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, tree_left INT NOT NULL, tree_right INT NOT NULL, tree_level INT NOT NULL, position INT NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E0307BBB77153098 ON listing_category (code)');
        $this->addSql('CREATE INDEX IDX_E0307BBBA977936C ON listing_category (tree_root)');
        $this->addSql('CREATE INDEX IDX_E0307BBB727ACA70 ON listing_category (parent_id)');
        $this->addSql('ALTER TABLE listing_attribute_category ADD CONSTRAINT FK_92BF28B0B6E62EFA FOREIGN KEY (attribute_id) REFERENCES listing_attribute (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE listing_attribute_category ADD CONSTRAINT FK_92BF28B012469DE2 FOREIGN KEY (category_id) REFERENCES listing_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE listing_category ADD CONSTRAINT FK_E0307BBBA977936C FOREIGN KEY (tree_root) REFERENCES sylius_taxon (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE listing_category ADD CONSTRAINT FK_E0307BBB727ACA70 FOREIGN KEY (parent_id) REFERENCES sylius_taxon (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('ALTER TABLE listing_attribute_category DROP CONSTRAINT FK_92BF28B0B6E62EFA');
        $this->addSql('ALTER TABLE listing_attribute_category DROP CONSTRAINT FK_92BF28B012469DE2');
        $this->addSql('DROP TABLE listing_attribute');
        $this->addSql('DROP TABLE listing_attribute_category');
        $this->addSql('DROP TABLE listing_category');
    }
}
