<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211109101659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE welldesign ADD price1km TEXT NOT NULL');
        $this->addSql('ALTER TABLE welldesign ADD price1pm TEXT NOT NULL');
        $this->addSql('ALTER TABLE welldesign DROP title');
        $this->addSql('ALTER TABLE welldesign DROP description');
        $this->addSql('ALTER TABLE welldesign DROP price');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('ALTER TABLE welldesign ADD title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE welldesign ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE welldesign ADD price INT NOT NULL');
        $this->addSql('ALTER TABLE welldesign DROP price1km');
        $this->addSql('ALTER TABLE welldesign DROP price1pm');
    }
}
