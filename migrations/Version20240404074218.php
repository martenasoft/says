<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240404074218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) DEFAULT NULL, is_bottom_menu TINYINT(1) NOT NULL, is_left_menu TINYINT(1) NOT NULL, is_top_menu TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, lvl INT NOT NULL, tree INT NOT NULL, parent_id INT NOT NULL, status SMALLINT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX lft (lft), INDEX lft_rgt (lft, rgt), INDEX id_lft_rgt (lft, rgt), INDEX is_bottom_menu (is_bottom_menu), INDEX is_left_menu (is_left_menu), INDEX is_top_menu (is_top_menu), UNIQUE INDEX UNIQ_7D053A93989D9B62B73E5EDC (slug, tree), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page DROP path');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE menu');
        $this->addSql('ALTER TABLE page ADD path VARCHAR(255) DEFAULT NULL');
    }
}
