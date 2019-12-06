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
final class Version20191206193446 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE recurring_event (id INT AUTO_INCREMENT NOT NULL, guild_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, create_in_advance_amount INT NOT NULL, date DATETIME NOT NULL, timezone VARCHAR(255) NOT NULL, last_event_start_date DATETIME NOT NULL, days JSON NOT NULL, week_interval INT NOT NULL, INDEX IDX_51B1C7F85F2131EF (guild_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recurring_event ADD CONSTRAINT FK_51B1C7F85F2131EF FOREIGN KEY (guild_id) REFERENCES discord_guild (id)');
        $this->addSql('ALTER TABLE event ADD recurring_parent_id INT DEFAULT NULL, CHANGE guild_id guild_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7185E564F FOREIGN KEY (recurring_parent_id) REFERENCES recurring_event (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7185E564F ON event (recurring_parent_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7185E564F');
        $this->addSql('DROP TABLE recurring_event');
        $this->addSql('DROP INDEX IDX_3BAE0AA7185E564F ON event');
        $this->addSql('ALTER TABLE event DROP recurring_parent_id, CHANGE guild_id guild_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
