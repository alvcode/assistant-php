<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260830215912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE drive_archive_files DROP COLUMN path");
        $this->addSql("ALTER TABLE drive_archive_files ALTER COLUMN size DROP NOT NULL");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE drive_archive_files ADD COLUMN path TEXT NOT NULL");
        $this->addSql("ALTER TABLE drive_archive_files ALTER COLUMN size SET NOT NULL");
    }
}
