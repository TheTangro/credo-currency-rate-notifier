<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220927215459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE proxy_stats DROP CONSTRAINT FK_8E3BCD87DB26A4E');
        $this->addSql('ALTER TABLE proxy_stats ADD CONSTRAINT FK_8E3BCD87DB26A4E FOREIGN KEY (proxy_id) REFERENCES proxy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE proxy_stats DROP CONSTRAINT fk_8e3bcd87db26a4e');
        $this->addSql('ALTER TABLE proxy_stats ADD CONSTRAINT fk_8e3bcd87db26a4e FOREIGN KEY (proxy_id) REFERENCES proxy (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
