<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210827130417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE welldesign (id SERIAL NOT NULL, listing_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, pipe_diameter VARCHAR(50) DEFAULT NULL, depth_from SMALLINT NOT NULL, depth_to SMALLINT NOT NULL, vehicle_type VARCHAR(50) DEFAULT NULL, price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_56345A8AD4619D1A ON welldesign (listing_id)');
        $this->addSql('ALTER TABLE welldesign ADD CONSTRAINT FK_56345A8AD4619D1A FOREIGN KEY (listing_id) REFERENCES listing (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE listing ADD telephone VARCHAR(35) DEFAULT NULL');
        $this->addSql('ALTER TABLE listing ADD image_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN listing.telephone IS \'(DC2Type:phone_number)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('DROP TABLE welldesign');
        $this->addSql('ALTER TABLE listing DROP telephone');
        $this->addSql('ALTER TABLE listing DROP image_name');
    }
}
