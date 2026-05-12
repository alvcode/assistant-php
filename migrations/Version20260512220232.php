<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512220232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE UNLOGGED TABLE rate_limiter(
                ip INET NOT NULL,
                allowance INT NOT NULL,
                timestamp BIGINT NOT NULL
            )
        ");
        $this->addSql("CREATE UNIQUE INDEX idx_rate_limiter_ip ON rate_limiter (ip)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TABLE IF EXISTS rate_limiter");
    }
}
