<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220927130849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE proxy_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE proxy_stats_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE proxy (id INT NOT NULL, ip BYTEA NOT NULL, port INT NOT NULL, type VARCHAR(10) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN proxy.ip IS \'(DC2Type:ip)\'');
        $this->addSql('CREATE TABLE proxy_stats (id INT NOT NULL, proxy_id INT NOT NULL, usage_counter INT NOT NULL, errors_counter INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8E3BCD87DB26A4E ON proxy_stats (proxy_id)');
        $this->addSql('ALTER TABLE proxy_stats ADD CONSTRAINT FK_8E3BCD87DB26A4E FOREIGN KEY (proxy_id) REFERENCES proxy (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE proxy_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE proxy_stats_id_seq CASCADE');
        $this->addSql('ALTER TABLE proxy_stats DROP CONSTRAINT FK_8E3BCD87DB26A4E');
        $this->addSql('DROP TABLE proxy');
        $this->addSql('DROP TABLE proxy_stats');
    }
}
