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
final class Version20191223120622 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE character_preset (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, class INT NOT NULL, role INT NOT NULL, INDEX IDX_8F1D27A1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE character_preset_armor_set (character_preset_id INT NOT NULL, armor_set_id INT NOT NULL, INDEX IDX_2DF846E27382C54 (character_preset_id), INDEX IDX_2DF846E2537E6F87 (armor_set_id), PRIMARY KEY(character_preset_id, armor_set_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE character_preset ADD CONSTRAINT FK_8F1D27A1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE character_preset_armor_set ADD CONSTRAINT FK_2DF846E27382C54 FOREIGN KEY (character_preset_id) REFERENCES character_preset (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_preset_armor_set ADD CONSTRAINT FK_2DF846E2537E6F87 FOREIGN KEY (armor_set_id) REFERENCES armor_set (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE character_preset_armor_set DROP FOREIGN KEY FK_2DF846E27382C54');
        $this->addSql('DROP TABLE character_preset');
        $this->addSql('DROP TABLE character_preset_armor_set');
    }
}
