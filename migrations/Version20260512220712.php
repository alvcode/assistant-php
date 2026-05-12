<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512220712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE drive_file_chunks(
                id SERIAL PRIMARY KEY,
                drive_file_id INT NOT NULL REFERENCES drive_files (id),
                path TEXT NOT NULL,
                size BIGINT NOT NULL,
                chunk_number INTEGER NOT NULL
            )
        ");
        $this->addSql("CREATE INDEX idx_drive_file_chunks_file_id ON drive_file_chunks (drive_file_id)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TABLE IF EXISTS drive_file_chunks");
    }
}
