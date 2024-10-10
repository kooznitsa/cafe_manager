<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241010105907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dishes ALTER price TYPE NUMERIC(15, 2)');
        $this->addSql('ALTER TABLE purchases ALTER price TYPE NUMERIC(15, 2)');
        $this->addSql('ALTER TABLE purchases ALTER amount TYPE NUMERIC(15, 2)');
        $this->addSql('DROP INDEX recipes__dish_id__product_id__unique');
        $this->addSql('ALTER TABLE recipes ALTER amount TYPE NUMERIC(15, 2)');
        $this->addSql('CREATE UNIQUE INDEX recipes__dish_id__product_id__unique ON recipes (product_id, dish_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE purchases ALTER price TYPE NUMERIC(53, 0)');
        $this->addSql('ALTER TABLE purchases ALTER amount TYPE NUMERIC(53, 0)');
        $this->addSql('ALTER TABLE dishes ALTER price TYPE NUMERIC(53, 0)');
        $this->addSql('DROP INDEX recipes__dish_id__product_id__unique');
        $this->addSql('ALTER TABLE recipes ALTER amount TYPE NUMERIC(53, 0)');
        $this->addSql('CREATE UNIQUE INDEX recipes__dish_id__product_id__unique ON recipes (dish_id, product_id)');
    }
}
