<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616143604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE location ADD result_data JSON DEFAULT NULL, DROP maps, DROP youtube, DROP weather
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location RENAME INDEX idx_5e9e89cb9d86650f TO IDX_5E9E89CBA76ED395
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE location ADD maps JSON NOT NULL, ADD youtube JSON NOT NULL, ADD weather JSON NOT NULL, DROP result_data
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location RENAME INDEX idx_5e9e89cba76ed395 TO IDX_5E9E89CB9D86650F
        SQL);
    }
}
