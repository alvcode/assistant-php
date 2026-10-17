<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260801202403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE drive_archive_files(
                id SERIAL PRIMARY KEY,
                drive_archive_job_id INT NOT NULL,
                path TEXT NOT NULL,
                size BIGINT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                CONSTRAINT drive_archive_files_job_id_fkey
                FOREIGN KEY (drive_archive_job_id)
                    REFERENCES drive_archive_jobs(id)
            )
        ");
        $this->addSql("CREATE INDEX idx_drive_af_daj_id ON drive_archive_files (drive_archive_job_id)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP INDEX idx_drive_af_daj_id");
        $this->addSql("DROP TABLE IF EXISTS drive_archive_files");
    }
}
