<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210720073054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE listing_taxon (listing_id INT NOT NULL, taxon_id INT NOT NULL, PRIMARY KEY(listing_id, taxon_id))');
        $this->addSql('CREATE INDEX IDX_AD89FB27D4619D1A ON listing_taxon (listing_id)');
        $this->addSql('CREATE INDEX IDX_AD89FB27DE13F470 ON listing_taxon (taxon_id)');
        $this->addSql('ALTER TABLE listing_taxon ADD CONSTRAINT FK_AD89FB27D4619D1A FOREIGN KEY (listing_id) REFERENCES listing (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE listing_taxon ADD CONSTRAINT FK_AD89FB27DE13F470 FOREIGN KEY (taxon_id) REFERENCES sylius_taxon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('DROP TABLE listing_taxon');
    }
}
