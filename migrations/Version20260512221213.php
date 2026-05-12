<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512221213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE drive_recycle_bin (
                id SERIAL PRIMARY KEY,
                drive_struct_id INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                CONSTRAINT drive_rb_drive_struct_id_fkey
                    FOREIGN KEY (drive_struct_id)
                        REFERENCES drive_structs(id)
                        ON DELETE CASCADE
            )
        ");
        $this->addSql("CREATE UNIQUE INDEX idx_drive_rb_drive_struct_id ON drive_recycle_bin (drive_struct_id)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP INDEX idx_drive_rb_drive_struct_id");
        $this->addSql("DROP TABLE IF EXISTS drive_recycle_bin");
    }
}
