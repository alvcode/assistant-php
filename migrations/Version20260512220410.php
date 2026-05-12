<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512220410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE drive_files(
                id SERIAL PRIMARY KEY,
                drive_struct_id INT NOT NULL REFERENCES drive_structs (id),
                path TEXT NOT NULL,
                ext TEXT,
                size BIGINT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
            )
        ");
        $this->addSql("CREATE UNIQUE INDEX idx_drive_files_drive_struct_id ON drive_files (drive_struct_id)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TABLE IF EXISTS drive_files");
    }
}
