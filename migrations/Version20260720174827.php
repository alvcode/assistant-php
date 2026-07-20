<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260720174827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE long_polling_events (
                id BIGSERIAL PRIMARY KEY,
                user_id INT NOT NULL,
                event_key TEXT NOT NULL,
                payload JSONB,
                expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
            )
        ");
        $this->addSql("CREATE UNIQUE INDEX idx_long_polling_user_id ON long_polling_events (user_id, id)");
        $this->addSql("CREATE INDEX idx_long_polling_user_expired ON long_polling_events (user_id, expired_at)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP INDEX idx_long_polling_user_expired");
        $this->addSql("DROP INDEX idx_long_polling_user_id");
        $this->addSql("DROP TABLE IF EXISTS long_polling_events");
    }
}
