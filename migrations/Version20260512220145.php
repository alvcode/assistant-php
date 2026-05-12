<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512220145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE file_note_links (
                file_id INT NOT NULL,
                note_id INT NOT NULL
            )
        ");
        $this->addSql("CREATE INDEX idx_file_note_links_file_id ON file_note_links (file_id)");
        $this->addSql("CREATE INDEX idx_file_note_links_note_id ON file_note_links (note_id)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TABLE IF EXISTS file_note_links");
    }
}
