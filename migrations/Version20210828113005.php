<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210828113005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE listing_subscription (id SERIAL NOT NULL, user_id INT DEFAULT NULL, product_id INT DEFAULT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, active BOOLEAN NOT NULL, auto_renewal BOOLEAN NOT NULL, reason VARCHAR(255) DEFAULT NULL, strategy VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_26F44AD2A76ED395 ON listing_subscription (user_id)');
        $this->addSql('CREATE INDEX IDX_26F44AD24584665A ON listing_subscription (product_id)');
        $this->addSql('ALTER TABLE listing_subscription ADD CONSTRAINT FK_26F44AD2A76ED395 FOREIGN KEY (user_id) REFERENCES api_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE listing_subscription ADD CONSTRAINT FK_26F44AD24584665A FOREIGN KEY (product_id) REFERENCES listing (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE listing ADD next_renewal_product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing ADD duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing ADD quota INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing ADD auto_renewal BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE listing ADD "default" BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE listing ADD expiration_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE listing ADD strategy_code_name VARCHAR(255) DEFAULT \'end_last\' NOT NULL');
        $this->addSql('ALTER TABLE listing ADD CONSTRAINT FK_CB0048D41F3B1C5E FOREIGN KEY (next_renewal_product_id) REFERENCES listing (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CB0048D41F3B1C5E ON listing (next_renewal_product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('DROP TABLE listing_subscription');
        $this->addSql('ALTER TABLE listing DROP CONSTRAINT FK_CB0048D41F3B1C5E');
        $this->addSql('DROP INDEX UNIQ_CB0048D41F3B1C5E');
        $this->addSql('ALTER TABLE listing DROP next_renewal_product_id');
        $this->addSql('ALTER TABLE listing DROP duration');
        $this->addSql('ALTER TABLE listing DROP quota');
        $this->addSql('ALTER TABLE listing DROP auto_renewal');
        $this->addSql('ALTER TABLE listing DROP "default"');
        $this->addSql('ALTER TABLE listing DROP expiration_date');
        $this->addSql('ALTER TABLE listing DROP strategy_code_name');
    }
}
