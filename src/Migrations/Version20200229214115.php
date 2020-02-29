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
final class Version20200229214115 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_attendee ADD character_preset_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event_attendee ADD CONSTRAINT FK_57BC3CB77382C54 FOREIGN KEY (character_preset_id) REFERENCES character_preset (id)');
        $this->addSql('CREATE INDEX IDX_57BC3CB77382C54 ON event_attendee (character_preset_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event_attendee DROP FOREIGN KEY FK_57BC3CB77382C54');
        $this->addSql('DROP INDEX IDX_57BC3CB77382C54 ON event_attendee');
        $this->addSql('ALTER TABLE event_attendee DROP character_preset_id');
    }
}
