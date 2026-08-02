<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260802182154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("TRUNCATE TABLE block_ip");
        $this->addSql("DROP INDEX IF EXISTS idx_block_ip_ip");
        $this->addSql("CREATE UNIQUE INDEX idx_block_ip_ip ON block_ip (ip)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("TRUNCATE TABLE block_ip");
        $this->addSql("DROP INDEX IF EXISTS idx_block_ip_ip");
        $this->addSql("CREATE INDEX idx_block_ip_ip ON block_ip (ip)");
    }
}
