<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20241009150927 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "users" (id BIGSERIAL NOT NULL, name VARCHAR(32) NOT NULL, password VARCHAR(32) NOT NULL, email VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE categories (id BIGSERIAL NOT NULL, name VARCHAR(32) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX categories__name__unique ON categories (name)');
        $this->addSql('CREATE TABLE dishes (id BIGSERIAL NOT NULL, name VARCHAR(255) NOT NULL, category_id BIGINT DEFAULT NULL, price DECIMAL(53) NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX dishes__name__category__unique ON dishes (name, category_id)');
        $this->addSql('ALTER TABLE dishes ADD CONSTRAINT dishes__category_id__fk FOREIGN KEY (category_id) REFERENCES "categories" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE TABLE products (id BIGSERIAL NOT NULL, name VARCHAR(255) NOT NULL, unit VARCHAR(32) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX products__name__unique ON products (name)');
        $this->addSql('CREATE TABLE recipes (id BIGSERIAL NOT NULL, dish_id BIGINT DEFAULT NULL, product_id BIGINT DEFAULT NULL, amount DECIMAL(53) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX recipes__dish_id__product_id__unique ON recipes (dish_id, product_id)');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT recipes__dish_id__fk FOREIGN KEY (dish_id) REFERENCES "dishes" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT recipes__product_id__fk FOREIGN KEY (product_id) REFERENCES "products" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE TABLE sales (id BIGSERIAL NOT NULL, dish_id BIGINT DEFAULT NULL, sold_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE sales ADD CONSTRAINT sales__dish_id__fk FOREIGN KEY (dish_id) REFERENCES "dishes" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE TABLE purchases (id BIGSERIAL NOT NULL, product_id BIGINT DEFAULT NULL, price DECIMAL(53) NOT NULL, amount DECIMAL(53) NOT NULL, purchased_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE purchases ADD CONSTRAINT purchases__product_id__fk FOREIGN KEY (product_id) REFERENCES "products" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "users"');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE dishes');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE recipes');
        $this->addSql('DROP TABLE sales');
        $this->addSql('DROP TABLE purchases');
    }
}
