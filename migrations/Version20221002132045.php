<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221002132045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE system_log');
        $this->addSql('DROP INDEX exchange_date_index');
        $this->addSql('CREATE INDEX exchange_date_index ON exchange_rate (exchange_type, currency_code, exchange_date)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE system_log (channel VARCHAR(255) DEFAULT NULL, level_name VARCHAR(10) DEFAULT NULL, message TEXT DEFAULT NULL, context JSON DEFAULT NULL, extra JSON DEFAULT NULL, datetime TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'now()\')');
        $this->addSql('CREATE INDEX system_log_channel_datetime_index ON system_log (channel, datetime)');
        $this->addSql('DROP INDEX exchange_date_index');
        $this->addSql('CREATE INDEX exchange_date_index ON exchange_rate (exchange_date, exchange_type)');
    }
}
