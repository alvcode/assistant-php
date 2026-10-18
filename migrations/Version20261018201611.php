<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261018201611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE drive_share_hashes (
            id SERIAL PRIMARY KEY,
            drive_struct_id INT NOT NULL,
            hash VARCHAR(80) NOT NULL,
            CONSTRAINT drive_share_hashes_struct_id_fkey
                FOREIGN KEY (drive_struct_id)
                    REFERENCES drive_structs(id)
                    ON DELETE CASCADE
        )
        ");
        $this->addSql("CREATE UNIQUE INDEX idx_drive_share_hashes_struct_id ON drive_share_hashes (drive_struct_id)");
        $this->addSql("CREATE UNIQUE INDEX idx_drive_share_hashes_hash ON drive_share_hashes (hash)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP INDEX idx_drive_share_hashes_hash");
        $this->addSql("DROP INDEX idx_drive_share_hashes_struct_id");
        $this->addSql("DROP TABLE IF EXISTS drive_share_hashes");
    }
}
