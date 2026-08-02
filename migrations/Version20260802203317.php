<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260802203317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE drive_archive_file_chunks (
                id SERIAL PRIMARY KEY,
                drive_archive_file_id INT NOT NULL,
                path TEXT NOT NULL,
                size BIGINT NOT NULL,
                chunk_number INT NOT NULL,
                CONSTRAINT drive_archive_file_chunks_file_id_fkey
                FOREIGN KEY (drive_archive_file_id)
                    REFERENCES drive_archive_files(id)
                    ON DELETE CASCADE
            )
        ");
        $this->addSql("CREATE INDEX idx_drive_afc_file_id ON drive_archive_file_chunks (drive_archive_file_id)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP INDEX idx_drive_afc_file_id");
        $this->addSql("DROP TABLE IF EXISTS drive_archive_file_chunks");
    }
}
