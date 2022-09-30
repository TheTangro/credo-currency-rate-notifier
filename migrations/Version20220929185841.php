<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220929185841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE exchange_rate_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE exchange_rate (id INT NOT NULL, currency_code VARCHAR(10) NOT NULL, buy_rate NUMERIC(20, 20) NOT NULL, sell_rate NUMERIC(20, 20) NOT NULL, exchange_type VARCHAR(16) NOT NULL, exchange_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX exchange_date_index ON exchange_rate (exchange_date, exchange_type)');
        $this->addSql('COMMENT ON COLUMN exchange_rate.exchange_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX proxy_stats_index ON proxy_stats (usage_counter, errors_counter)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE exchange_rate_id_seq CASCADE');
        $this->addSql('DROP TABLE exchange_rate');
        $this->addSql('DROP INDEX proxy_stats_index');
    }
}
