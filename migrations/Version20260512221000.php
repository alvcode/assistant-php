<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512221000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE note_share_hashes (
            id SERIAL PRIMARY KEY,
            note_id INT NOT NULL,
            hash VARCHAR(80) NOT NULL,
            CONSTRAINT note_share_hashes_note_id_fkey
                FOREIGN KEY (note_id)
                    REFERENCES notes(id)
                    ON DELETE CASCADE
        )
        ");
        $this->addSql("CREATE UNIQUE INDEX idx_note_share_hashes_note_id ON note_share_hashes (note_id)");
        $this->addSql("CREATE UNIQUE INDEX idx_note_share_hashes_hash ON note_share_hashes (hash)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP INDEX idx_note_share_hashes_hash");
        $this->addSql("DROP INDEX idx_note_share_hashes_note_id");
        $this->addSql("DROP TABLE IF EXISTS note_share_hashes");
    }
}
