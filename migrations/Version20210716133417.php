<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716133417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE business_taxon (business_id INT NOT NULL, taxon_id INT NOT NULL, PRIMARY KEY(business_id, taxon_id))');
        $this->addSql('CREATE INDEX IDX_2586C414A89DB457 ON business_taxon (business_id)');
        $this->addSql('CREATE INDEX IDX_2586C414DE13F470 ON business_taxon (taxon_id)');
        $this->addSql('ALTER TABLE business_taxon ADD CONSTRAINT FK_2586C414A89DB457 FOREIGN KEY (business_id) REFERENCES business (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_taxon ADD CONSTRAINT FK_2586C414DE13F470 FOREIGN KEY (taxon_id) REFERENCES sylius_taxon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('DROP TABLE business_taxon');
    }
}
