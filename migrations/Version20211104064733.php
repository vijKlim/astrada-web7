<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211104064733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE listing_pricing_rule (id SERIAL NOT NULL, rule_set_id INT NOT NULL, expression TEXT NOT NULL, price TEXT NOT NULL, position INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E8FC88738B51FD88 ON listing_pricing_rule (rule_set_id)');
        $this->addSql('CREATE TABLE listing_pricing_rule_set (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, strategy VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE listing_pricing_rule ADD CONSTRAINT FK_E8FC88738B51FD88 FOREIGN KEY (rule_set_id) REFERENCES listing_pricing_rule_set (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('ALTER TABLE listing_pricing_rule DROP CONSTRAINT FK_E8FC88738B51FD88');
        $this->addSql('DROP TABLE listing_pricing_rule');
        $this->addSql('DROP TABLE listing_pricing_rule_set');
    }
}
