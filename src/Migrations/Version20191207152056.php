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
final class Version20191207152056 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE armor_set (id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recurring_event (id INT AUTO_INCREMENT NOT NULL, guild_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, create_in_advance_amount INT NOT NULL, date DATETIME NOT NULL, timezone VARCHAR(255) NOT NULL, last_event_start_date DATETIME NOT NULL, days JSON NOT NULL, week_interval INT NOT NULL, INDEX IDX_51B1C7F85F2131EF (guild_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discord_guild (id VARCHAR(255) NOT NULL, owner_id INT DEFAULT NULL, log_channel VARCHAR(255) DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, bot_active TINYINT(1) NOT NULL, INDEX IDX_7539ABA87E3C61F9 (owner_id), UNIQUE INDEX UNIQ_7539ABA89A34B6D0 (log_channel), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_attendee (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, event_id INT DEFAULT NULL, status INT NOT NULL, class INT NOT NULL, role INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_57BC3CB7A76ED395 (user_id), INDEX IDX_57BC3CB771F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_attendee_armor_set (event_attendee_id INT NOT NULL, armor_set_id INT NOT NULL, INDEX IDX_D716C1D11774ABAA (event_attendee_id), INDEX IDX_D716C1D1537E6F87 (armor_set_id), PRIMARY KEY(event_attendee_id, armor_set_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discord_channel (id VARCHAR(255) NOT NULL, guild_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, type INT NOT NULL, error INT NOT NULL, INDEX IDX_E664AA1C5F2131EF (guild_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, guild_id VARCHAR(255) DEFAULT NULL, recurring_parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, start DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, locked TINYINT(1) NOT NULL, tags JSON NOT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_3BAE0AA75F2131EF (guild_id), INDEX IDX_3BAE0AA7185E564F (recurring_parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guild_membership (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, guild_id VARCHAR(255) NOT NULL, role INT NOT NULL, INDEX IDX_E7D8D2AA76ED395 (user_id), INDEX IDX_E7D8D2A5F2131EF (guild_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reminder (id INT AUTO_INCREMENT NOT NULL, discord_channel_id VARCHAR(255) DEFAULT NULL, guild_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, minutes_to_trigger INT NOT NULL, detailed_info TINYINT(1) NOT NULL, ping_attendees TINYINT(1) NOT NULL, INDEX IDX_40374F406D4A6EE0 (discord_channel_id), INDEX IDX_40374F405F2131EF (guild_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, discord_discriminator VARCHAR(40) NOT NULL, email VARCHAR(255) NOT NULL, avatar VARCHAR(255) NOT NULL, discord_id VARCHAR(255) NOT NULL, clock INT NOT NULL, timezone VARCHAR(255) NOT NULL, darkmode TINYINT(1) NOT NULL, discord_token VARCHAR(255) NOT NULL, discord_refresh_token VARCHAR(255) NOT NULL, discord_token_expiration_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guild_log (id INT AUTO_INCREMENT NOT NULL, channel VARCHAR(255) NOT NULL, data JSON NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recurring_event ADD CONSTRAINT FK_51B1C7F85F2131EF FOREIGN KEY (guild_id) REFERENCES discord_guild (id)');
        $this->addSql('ALTER TABLE discord_guild ADD CONSTRAINT FK_7539ABA87E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE discord_guild ADD CONSTRAINT FK_7539ABA89A34B6D0 FOREIGN KEY (log_channel) REFERENCES discord_channel (id)');
        $this->addSql('ALTER TABLE event_attendee ADD CONSTRAINT FK_57BC3CB7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event_attendee ADD CONSTRAINT FK_57BC3CB771F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event_attendee_armor_set ADD CONSTRAINT FK_D716C1D11774ABAA FOREIGN KEY (event_attendee_id) REFERENCES event_attendee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_attendee_armor_set ADD CONSTRAINT FK_D716C1D1537E6F87 FOREIGN KEY (armor_set_id) REFERENCES armor_set (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE discord_channel ADD CONSTRAINT FK_E664AA1C5F2131EF FOREIGN KEY (guild_id) REFERENCES discord_guild (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA75F2131EF FOREIGN KEY (guild_id) REFERENCES discord_guild (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7185E564F FOREIGN KEY (recurring_parent_id) REFERENCES recurring_event (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE guild_membership ADD CONSTRAINT FK_E7D8D2AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE guild_membership ADD CONSTRAINT FK_E7D8D2A5F2131EF FOREIGN KEY (guild_id) REFERENCES discord_guild (id)');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F406D4A6EE0 FOREIGN KEY (discord_channel_id) REFERENCES discord_channel (id)');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F405F2131EF FOREIGN KEY (guild_id) REFERENCES discord_guild (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_attendee_armor_set DROP FOREIGN KEY FK_D716C1D1537E6F87');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7185E564F');
        $this->addSql('ALTER TABLE recurring_event DROP FOREIGN KEY FK_51B1C7F85F2131EF');
        $this->addSql('ALTER TABLE discord_channel DROP FOREIGN KEY FK_E664AA1C5F2131EF');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA75F2131EF');
        $this->addSql('ALTER TABLE guild_membership DROP FOREIGN KEY FK_E7D8D2A5F2131EF');
        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F405F2131EF');
        $this->addSql('ALTER TABLE event_attendee_armor_set DROP FOREIGN KEY FK_D716C1D11774ABAA');
        $this->addSql('ALTER TABLE discord_guild DROP FOREIGN KEY FK_7539ABA89A34B6D0');
        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F406D4A6EE0');
        $this->addSql('ALTER TABLE event_attendee DROP FOREIGN KEY FK_57BC3CB771F7E88B');
        $this->addSql('ALTER TABLE discord_guild DROP FOREIGN KEY FK_7539ABA87E3C61F9');
        $this->addSql('ALTER TABLE event_attendee DROP FOREIGN KEY FK_57BC3CB7A76ED395');
        $this->addSql('ALTER TABLE guild_membership DROP FOREIGN KEY FK_E7D8D2AA76ED395');
        $this->addSql('DROP TABLE armor_set');
        $this->addSql('DROP TABLE recurring_event');
        $this->addSql('DROP TABLE discord_guild');
        $this->addSql('DROP TABLE event_attendee');
        $this->addSql('DROP TABLE event_attendee_armor_set');
        $this->addSql('DROP TABLE discord_channel');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE guild_membership');
        $this->addSql('DROP TABLE reminder');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE guild_log');
    }
}
