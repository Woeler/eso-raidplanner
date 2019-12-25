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
final class Version20191225121948 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE discord_guild DROP FOREIGN KEY FK_7539ABA89A34B6D0');
        $this->addSql('ALTER TABLE discord_guild ADD event_create_channel VARCHAR(255) DEFAULT NULL, CHANGE owner_id owner_id INT DEFAULT NULL, CHANGE log_channel log_channel VARCHAR(255) DEFAULT NULL, CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE discord_guild ADD CONSTRAINT FK_7539ABA85F85AF33 FOREIGN KEY (event_create_channel) REFERENCES discord_channel (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE discord_guild ADD CONSTRAINT FK_7539ABA89A34B6D0 FOREIGN KEY (log_channel) REFERENCES discord_channel (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7539ABA85F85AF33 ON discord_guild (event_create_channel)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE discord_guild DROP FOREIGN KEY FK_7539ABA85F85AF33');
        $this->addSql('ALTER TABLE discord_guild DROP FOREIGN KEY FK_7539ABA89A34B6D0');
        $this->addSql('DROP INDEX UNIQ_7539ABA85F85AF33 ON discord_guild');
        $this->addSql('ALTER TABLE discord_guild DROP event_create_channel, CHANGE owner_id owner_id INT DEFAULT NULL, CHANGE log_channel log_channel VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE icon icon VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE discord_guild ADD CONSTRAINT FK_7539ABA89A34B6D0 FOREIGN KEY (log_channel) REFERENCES discord_channel (id)');
    }
}
