<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512204830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE TABLE user_tokens (
               user_id INT NOT NULL,
               token VARCHAR(100) NOT NULL,
               refresh_token VARCHAR(100) NOT NULL,
               expired_to INT NOT NULL
            )
        ");
        $this->addSql("CREATE UNIQUE INDEX idx_user_tokens_token ON user_tokens (token)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS user_tokens');
    }
}
