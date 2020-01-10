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
final class Version20200110091900 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F406D4A6EE0');
        $this->addSql('ALTER TABLE reminder CHANGE discord_channel_id discord_channel_id VARCHAR(255) DEFAULT NULL, CHANGE guild_id guild_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F406D4A6EE0 FOREIGN KEY (discord_channel_id) REFERENCES discord_channel (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F406D4A6EE0');
        $this->addSql('ALTER TABLE reminder CHANGE discord_channel_id discord_channel_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE guild_id guild_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F406D4A6EE0 FOREIGN KEY (discord_channel_id) REFERENCES discord_channel (id)');
    }
}
