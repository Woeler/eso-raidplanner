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
final class Version20201125213329 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE poll (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, question VARCHAR(255) NOT NULL, multiple_choice TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_84BCFA4571F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE poll_option (id INT AUTO_INCREMENT NOT NULL, poll_id INT NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_B68343EB3C947C0F (poll_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE poll_vote (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, poll_id INT NOT NULL, poll_option_id INT NOT NULL, INDEX IDX_ED568EBEA76ED395 (user_id), INDEX IDX_ED568EBE3C947C0F (poll_id), INDEX IDX_ED568EBE6C13349B (poll_option_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT FK_84BCFA4571F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE poll_option ADD CONSTRAINT FK_B68343EB3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE6C13349B FOREIGN KEY (poll_option_id) REFERENCES poll_option (id)');
        $this->addSql('ALTER TABLE event ADD poll_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA73C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA73C947C0F ON event (poll_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA73C947C0F');
        $this->addSql('ALTER TABLE poll_option DROP FOREIGN KEY FK_B68343EB3C947C0F');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE3C947C0F');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE6C13349B');
        $this->addSql('DROP TABLE poll');
        $this->addSql('DROP TABLE poll_option');
        $this->addSql('DROP TABLE poll_vote');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA73C947C0F ON event');
        $this->addSql('ALTER TABLE event DROP poll_id');
    }
}
