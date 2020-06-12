<?php

declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200612094335 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE recurring_event ADD reminder_reroute_channel_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE recurring_event ADD CONSTRAINT FK_51B1C7F82AF7F5C4 FOREIGN KEY (reminder_reroute_channel_id) REFERENCES discord_channel (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_51B1C7F82AF7F5C4 ON recurring_event (reminder_reroute_channel_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE recurring_event DROP FOREIGN KEY FK_51B1C7F82AF7F5C4');
        $this->addSql('DROP INDEX IDX_51B1C7F82AF7F5C4 ON recurring_event');
        $this->addSql('ALTER TABLE recurring_event DROP reminder_reroute_channel_id');
    }
}
