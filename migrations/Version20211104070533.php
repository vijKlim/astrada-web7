<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211104070533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE listing_pricing_rule_set ADD business_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_pricing_rule_set ADD CONSTRAINT FK_2B0BBD97A89DB457 FOREIGN KEY (business_id) REFERENCES business (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2B0BBD97A89DB457 ON listing_pricing_rule_set (business_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('ALTER TABLE listing_pricing_rule_set DROP CONSTRAINT FK_2B0BBD97A89DB457');
        $this->addSql('DROP INDEX IDX_2B0BBD97A89DB457');
        $this->addSql('ALTER TABLE listing_pricing_rule_set DROP business_id');
    }
}
