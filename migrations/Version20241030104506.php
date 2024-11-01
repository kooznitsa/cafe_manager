<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241030104506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders ALTER status TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE orders ALTER is_delivery SET DEFAULT false');
        $this->addSql('ALTER TABLE users ADD roles JSON NOT NULL');
        $this->addSql('ALTER TABLE users ALTER password TYPE VARCHAR(120)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "users" DROP roles');
        $this->addSql('ALTER TABLE "users" ALTER password TYPE VARCHAR(32)');
        $this->addSql('ALTER TABLE orders ALTER status TYPE VARCHAR(32)');
        $this->addSql('ALTER TABLE orders ALTER is_delivery DROP DEFAULT');
    }
}
