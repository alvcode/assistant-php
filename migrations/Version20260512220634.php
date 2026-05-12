<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512220634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE drive_files ALTER COLUMN path DROP NOT NULL");
        $this->addSql("ALTER TABLE drive_files ADD COLUMN is_chunk BOOLEAN NOT NULL DEFAULT (false)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE drive_files ALTER COLUMN path SET NOT NULL");
        $this->addSql("ALTER TABLE drive_files DROP COLUMN is_chunk");
    }
}
