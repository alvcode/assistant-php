<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512220448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE drive_files DROP CONSTRAINT drive_files_drive_struct_id_fkey");
        $this->addSql("
            ALTER TABLE drive_files
            ADD CONSTRAINT drive_files_drive_struct_id_fkey
                FOREIGN KEY (drive_struct_id)
                    REFERENCES drive_structs(id)
                    ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE drive_files DROP CONSTRAINT drive_files_drive_struct_id_fkey");
        $this->addSql("
            ALTER TABLE drive_files
            ADD CONSTRAINT drive_files_drive_struct_id_fkey
                FOREIGN KEY (drive_struct_id)
                    REFERENCES drive_structs(id)
        ");
    }
}
