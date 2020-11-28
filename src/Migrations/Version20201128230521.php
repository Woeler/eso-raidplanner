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
final class Version20201128230521 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA4571F7E88B');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT FK_84BCFA4571F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE poll_option DROP FOREIGN KEY FK_B68343EB3C947C0F');
        $this->addSql('ALTER TABLE poll_option ADD CONSTRAINT FK_B68343EB3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE3C947C0F');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE6C13349B');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBEA76ED395');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE6C13349B FOREIGN KEY (poll_option_id) REFERENCES poll_option (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE poll DROP FOREIGN KEY FK_84BCFA4571F7E88B');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT FK_84BCFA4571F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE poll_option DROP FOREIGN KEY FK_B68343EB3C947C0F');
        $this->addSql('ALTER TABLE poll_option ADD CONSTRAINT FK_B68343EB3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBEA76ED395');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE3C947C0F');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE6C13349B');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE6C13349B FOREIGN KEY (poll_option_id) REFERENCES poll_option (id)');
    }
}
