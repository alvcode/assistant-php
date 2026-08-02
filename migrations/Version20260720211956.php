<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260720211956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE drive_archive_jobs (
                id SERIAL PRIMARY KEY,
                user_id INT NOT NULL,
                struct_ids JSONB NOT NULL,
                status INT NOT NULL,
                error_description TEXT,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                finished_at TIMESTAMP(0) WITHOUT TIME ZONE
            )
        ");

        $this->addSql("CREATE INDEX idx_drive_aj_user_status ON drive_archive_jobs (user_id, status)");
        $this->addSql("CREATE INDEX idx_drive_aj_status ON drive_archive_jobs (status)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP INDEX idx_drive_aj_status");
        $this->addSql("DROP INDEX idx_drive_aj_user_status");
        $this->addSql("DROP TABLE IF EXISTS drive_archive_jobs");
    }
}
