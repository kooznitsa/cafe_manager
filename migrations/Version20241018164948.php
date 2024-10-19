<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241018164948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE sales_id_seq CASCADE');
        $this->addSql('CREATE TABLE orders (
          id BIGSERIAL NOT NULL,
          dish_id BIGINT DEFAULT NULL,
          user_id BIGINT DEFAULT NULL,
          status VARCHAR(32) NOT NULL,
          delivery BOOLEAN NOT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_E52FFDEE148EB0CB ON orders (dish_id)');
        $this->addSql('CREATE INDEX IDX_E52FFDEEA76ED395 ON orders (user_id)');
        $this->addSql('ALTER TABLE
          orders
        ADD
          CONSTRAINT FK_E52FFDEE148EB0CB FOREIGN KEY (dish_id) REFERENCES dishes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          orders
        ADD
          CONSTRAINT FK_E52FFDEEA76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sales DROP CONSTRAINT sales__dish_id__fk');
        $this->addSql('DROP TABLE sales');
        $this->addSql('ALTER INDEX users__email__uniq RENAME TO users__email__unique');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE sales_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE sales (
          id BIGSERIAL NOT NULL,
          dish_id BIGINT DEFAULT NULL,
          sold_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_6B817044148EB0CB ON sales (dish_id)');
        $this->addSql('ALTER TABLE
          sales
        ADD
          CONSTRAINT sales__dish_id__fk FOREIGN KEY (dish_id) REFERENCES dishes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEE148EB0CB');
        $this->addSql('ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEEA76ED395');
        $this->addSql('DROP TABLE orders');
        $this->addSql('ALTER INDEX users__email__unique RENAME TO users__email__uniq');
    }
}
