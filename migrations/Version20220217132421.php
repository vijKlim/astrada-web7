<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220217132421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE delivery (id INT NOT NULL, order_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3781EC108D9F6D38 ON delivery (order_id)');
        $this->addSql('CREATE TABLE sylius_adjustment (id SERIAL NOT NULL, order_id INT DEFAULT NULL, order_item_id INT DEFAULT NULL, order_item_unit_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, label VARCHAR(255) DEFAULT NULL, amount INT NOT NULL, is_neutral BOOLEAN NOT NULL, is_locked BOOLEAN NOT NULL, origin_code VARCHAR(255) DEFAULT NULL, details JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ACA6E0F28D9F6D38 ON sylius_adjustment (order_id)');
        $this->addSql('CREATE INDEX IDX_ACA6E0F2E415FB15 ON sylius_adjustment (order_item_id)');
        $this->addSql('CREATE INDEX IDX_ACA6E0F2F720C233 ON sylius_adjustment (order_item_unit_id)');
        $this->addSql('CREATE TABLE sylius_channel (id SERIAL NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, enabled BOOLEAN NOT NULL, hostname VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16C8119E77153098 ON sylius_channel (code)');
        $this->addSql('CREATE INDEX IDX_16C8119EE551C011 ON sylius_channel (hostname)');
        $this->addSql('CREATE TABLE sylius_order (id SERIAL NOT NULL, business_id INT DEFAULT NULL, shipping_address_id INT DEFAULT NULL, billing_address_id INT DEFAULT NULL, channel_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, number VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, state VARCHAR(255) NOT NULL, checkout_completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, items_total INT NOT NULL, adjustments_total INT NOT NULL, total INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, takeaway BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6196A1F996901F54 ON sylius_order (number)');
        $this->addSql('CREATE INDEX IDX_6196A1F9A89DB457 ON sylius_order (business_id)');
        $this->addSql('CREATE INDEX IDX_6196A1F94D4CFF2B ON sylius_order (shipping_address_id)');
        $this->addSql('CREATE INDEX IDX_6196A1F979D0C0E4 ON sylius_order (billing_address_id)');
        $this->addSql('CREATE INDEX IDX_6196A1F972F5A1AA ON sylius_order (channel_id)');
        $this->addSql('CREATE INDEX IDX_6196A1F99395C3F3 ON sylius_order (customer_id)');
        $this->addSql('CREATE INDEX IDX_6196A1F9A393D2FB43625D9F ON sylius_order (state, updated_at)');
        $this->addSql('CREATE TABLE sylius_order_event (id SERIAL NOT NULL, aggregate_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, data JSON NOT NULL, metadata JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1F7207A3D0BBCCBE ON sylius_order_event (aggregate_id)');
        $this->addSql('COMMENT ON COLUMN sylius_order_event.data IS \'(DC2Type:json_array)\'');
        $this->addSql('COMMENT ON COLUMN sylius_order_event.metadata IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE sylius_order_item (id SERIAL NOT NULL, order_id INT NOT NULL, variant_id INT NOT NULL, quantity INT NOT NULL, unit_price INT NOT NULL, units_total INT NOT NULL, adjustments_total INT NOT NULL, total INT NOT NULL, is_immutable BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_77B587ED8D9F6D38 ON sylius_order_item (order_id)');
        $this->addSql('CREATE INDEX IDX_77B587ED3B69A9AF ON sylius_order_item (variant_id)');
        $this->addSql('CREATE TABLE sylius_order_item_unit (id SERIAL NOT NULL, order_item_id INT NOT NULL, adjustments_total INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_82BF226EE415FB15 ON sylius_order_item_unit (order_item_id)');
        $this->addSql('CREATE TABLE sylius_order_sequence (id SERIAL NOT NULL, idx INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE sylius_payment (id SERIAL NOT NULL, method_id INT DEFAULT NULL, order_id INT NOT NULL, currency_code VARCHAR(3) NOT NULL, amount INT NOT NULL, state VARCHAR(255) NOT NULL, details JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D9191BD419883967 ON sylius_payment (method_id)');
        $this->addSql('CREATE INDEX IDX_D9191BD48D9F6D38 ON sylius_payment (order_id)');
        $this->addSql('COMMENT ON COLUMN sylius_payment.details IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE sylius_payment_method (id SERIAL NOT NULL, code VARCHAR(255) NOT NULL, environment VARCHAR(255) DEFAULT NULL, is_enabled BOOLEAN NOT NULL, position INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A75B0B0D77153098 ON sylius_payment_method (code)');
        $this->addSql('CREATE TABLE sylius_payment_method_translation (id SERIAL NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, instructions TEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_966BE3A12C2AC5D3 ON sylius_payment_method_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX sylius_payment_method_translation_uniq_trans ON sylius_payment_method_translation (translatable_id, locale)');
        $this->addSql('CREATE TABLE task (id SERIAL NOT NULL, next_task_id INT DEFAULT NULL, previous_task_id INT DEFAULT NULL, delivery_id INT DEFAULT NULL, address_id INT NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, done_after TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, done_before TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, comments TEXT DEFAULT NULL, metadata JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_527EDB254F382B24 ON task (next_task_id)');
        $this->addSql('CREATE INDEX IDX_527EDB25BC2D6B55 ON task (previous_task_id)');
        $this->addSql('CREATE INDEX IDX_527EDB2512136921 ON task (delivery_id)');
        $this->addSql('CREATE INDEX IDX_527EDB25F5B7AF75 ON task (address_id)');
        $this->addSql('COMMENT ON COLUMN task.metadata IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE task_collection (id SERIAL NOT NULL, distance INT NOT NULL, duration INT NOT NULL, polyline TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE task_collection_item (id SERIAL NOT NULL, parent_id INT DEFAULT NULL, task_id INT DEFAULT NULL, position INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_13199EFF727ACA70 ON task_collection_item (parent_id)');
        $this->addSql('CREATE INDEX IDX_13199EFF8DB60186 ON task_collection_item (task_id)');
        $this->addSql('CREATE UNIQUE INDEX task_collection_item_unique ON task_collection_item (parent_id, task_id)');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC108D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10BF396750 FOREIGN KEY (id) REFERENCES task_collection (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_adjustment ADD CONSTRAINT FK_ACA6E0F28D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_adjustment ADD CONSTRAINT FK_ACA6E0F2E415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_adjustment ADD CONSTRAINT FK_ACA6E0F2F720C233 FOREIGN KEY (order_item_unit_id) REFERENCES sylius_order_item_unit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_order ADD CONSTRAINT FK_6196A1F9A89DB457 FOREIGN KEY (business_id) REFERENCES business (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_order ADD CONSTRAINT FK_6196A1F94D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_order ADD CONSTRAINT FK_6196A1F979D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_order ADD CONSTRAINT FK_6196A1F972F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_order ADD CONSTRAINT FK_6196A1F99395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_order_event ADD CONSTRAINT FK_1F7207A3D0BBCCBE FOREIGN KEY (aggregate_id) REFERENCES sylius_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_order_item ADD CONSTRAINT FK_77B587ED8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_order_item ADD CONSTRAINT FK_77B587ED3B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_order_item_unit ADD CONSTRAINT FK_82BF226EE415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_payment ADD CONSTRAINT FK_D9191BD419883967 FOREIGN KEY (method_id) REFERENCES sylius_payment_method (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_payment ADD CONSTRAINT FK_D9191BD48D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_payment_method_translation ADD CONSTRAINT FK_966BE3A12C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES sylius_payment_method (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB254F382B24 FOREIGN KEY (next_task_id) REFERENCES task (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25BC2D6B55 FOREIGN KEY (previous_task_id) REFERENCES task (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2512136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task_collection_item ADD CONSTRAINT FK_13199EFF727ACA70 FOREIGN KEY (parent_id) REFERENCES task_collection (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task_collection_item ADD CONSTRAINT FK_13199EFF8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA topology');
        $this->addSql('CREATE SCHEMA tiger');
        $this->addSql('CREATE SCHEMA tiger_data');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB2512136921');
        $this->addSql('ALTER TABLE sylius_order DROP CONSTRAINT FK_6196A1F972F5A1AA');
        $this->addSql('ALTER TABLE delivery DROP CONSTRAINT FK_3781EC108D9F6D38');
        $this->addSql('ALTER TABLE sylius_adjustment DROP CONSTRAINT FK_ACA6E0F28D9F6D38');
        $this->addSql('ALTER TABLE sylius_order_event DROP CONSTRAINT FK_1F7207A3D0BBCCBE');
        $this->addSql('ALTER TABLE sylius_order_item DROP CONSTRAINT FK_77B587ED8D9F6D38');
        $this->addSql('ALTER TABLE sylius_payment DROP CONSTRAINT FK_D9191BD48D9F6D38');
        $this->addSql('ALTER TABLE sylius_adjustment DROP CONSTRAINT FK_ACA6E0F2E415FB15');
        $this->addSql('ALTER TABLE sylius_order_item_unit DROP CONSTRAINT FK_82BF226EE415FB15');
        $this->addSql('ALTER TABLE sylius_adjustment DROP CONSTRAINT FK_ACA6E0F2F720C233');
        $this->addSql('ALTER TABLE sylius_payment DROP CONSTRAINT FK_D9191BD419883967');
        $this->addSql('ALTER TABLE sylius_payment_method_translation DROP CONSTRAINT FK_966BE3A12C2AC5D3');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB254F382B24');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB25BC2D6B55');
        $this->addSql('ALTER TABLE task_collection_item DROP CONSTRAINT FK_13199EFF8DB60186');
        $this->addSql('ALTER TABLE delivery DROP CONSTRAINT FK_3781EC10BF396750');
        $this->addSql('ALTER TABLE task_collection_item DROP CONSTRAINT FK_13199EFF727ACA70');
        $this->addSql('DROP TABLE delivery');
        $this->addSql('DROP TABLE sylius_adjustment');
        $this->addSql('DROP TABLE sylius_channel');
        $this->addSql('DROP TABLE sylius_order');
        $this->addSql('DROP TABLE sylius_order_event');
        $this->addSql('DROP TABLE sylius_order_item');
        $this->addSql('DROP TABLE sylius_order_item_unit');
        $this->addSql('DROP TABLE sylius_order_sequence');
        $this->addSql('DROP TABLE sylius_payment');
        $this->addSql('DROP TABLE sylius_payment_method');
        $this->addSql('DROP TABLE sylius_payment_method_translation');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE task_collection');
        $this->addSql('DROP TABLE task_collection_item');
    }
}
