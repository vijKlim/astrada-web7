<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211122150159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_app (id SERIAL NOT NULL, business_id INT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(12) NOT NULL, api_key VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8AEC36FBA89DB457 ON api_app (business_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8AEC36FBC912ED9D ON api_app (api_key)');
        $this->addSql('CREATE TABLE listing_image (id SERIAL NOT NULL, listing_id INT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, ratio VARCHAR(5) NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_33D3DCD3D4619D1A ON listing_image (listing_id)');
        $this->addSql('ALTER TABLE api_app ADD CONSTRAINT FK_8AEC36FBA89DB457 FOREIGN KEY (business_id) REFERENCES business (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE listing_image ADD CONSTRAINT FK_33D3DCD3D4619D1A FOREIGN KEY (listing_id) REFERENCES listing (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE listing DROP image_name');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('DROP TABLE api_app');
        $this->addSql('DROP TABLE listing_image');
        $this->addSql('ALTER TABLE listing ADD image_name VARCHAR(255) DEFAULT NULL');
    }
}
