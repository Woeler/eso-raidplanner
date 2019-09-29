<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190928151652 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE discord_guild ADD log_channel VARCHAR(255) DEFAULT NULL, CHANGE owner_id owner_id INT DEFAULT NULL, CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE discord_guild ADD CONSTRAINT FK_7539ABA89A34B6D0 FOREIGN KEY (log_channel) REFERENCES discord_channel (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7539ABA89A34B6D0 ON discord_guild (log_channel)');
        $this->addSql('ALTER TABLE event_attendee CHANGE user_id user_id INT DEFAULT NULL, CHANGE event_id event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event CHANGE guild_id guild_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reminder CHANGE discord_channel_id discord_channel_id VARCHAR(255) DEFAULT NULL, CHANGE guild_id guild_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE discord_guild DROP FOREIGN KEY FK_7539ABA89A34B6D0');
        $this->addSql('DROP INDEX UNIQ_7539ABA89A34B6D0 ON discord_guild');
        $this->addSql('ALTER TABLE discord_guild DROP log_channel, CHANGE owner_id owner_id INT DEFAULT NULL, CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE event CHANGE guild_id guild_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE event_attendee CHANGE user_id user_id INT DEFAULT NULL, CHANGE event_id event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reminder CHANGE discord_channel_id discord_channel_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE guild_id guild_id VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
