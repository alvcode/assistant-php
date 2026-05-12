<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512220051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE files (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL,
                original_filename TEXT NOT NULL,
                file_path TEXT NOT NULL,
                ext TEXT NOT NULL,
                size INT NOT NULL,
                hash VARCHAR(80) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
            )
        ");
        $this->addSql("CREATE INDEX idx_files_user_id ON files (user_id)");
        $this->addSql("CREATE UNIQUE INDEX idx_files_hash ON files (hash)");
        $this->addSql("CREATE INDEX idx_files_size ON files (size)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TABLE IF EXISTS files");
    }
}
