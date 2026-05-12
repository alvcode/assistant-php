<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512215820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE block_ip (
                  id SERIAL PRIMARY KEY,
                  ip INET NOT NULL,
                  blocked_until TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
            )
        ");
        $this->addSql("CREATE INDEX idx_block_ip_ip ON block_ip (ip)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TABLE IF EXISTS block_ip");
    }
}
