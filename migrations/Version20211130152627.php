<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211130152627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking ADD business_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA89DB457 FOREIGN KEY (business_id) REFERENCES business (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA89DB457 ON booking (business_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT FK_E00CEDDEA89DB457');
        $this->addSql('DROP INDEX IDX_E00CEDDEA89DB457');
        $this->addSql('ALTER TABLE booking DROP business_id');
    }
}
