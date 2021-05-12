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
final class Version20210512083353 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_attendee DROP FOREIGN KEY FK_57BC3CB77382C54');
        $this->addSql('ALTER TABLE event_attendee ADD CONSTRAINT FK_57BC3CB77382C54 FOREIGN KEY (character_preset_id) REFERENCES character_preset (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reminder CHANGE discord_channel_id discord_channel_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_attendee DROP FOREIGN KEY FK_57BC3CB77382C54');
        $this->addSql('ALTER TABLE event_attendee ADD CONSTRAINT FK_57BC3CB77382C54 FOREIGN KEY (character_preset_id) REFERENCES character_preset (id)');
        $this->addSql('ALTER TABLE reminder CHANGE discord_channel_id discord_channel_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
