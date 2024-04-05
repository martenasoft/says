<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240404125458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620727ACA70');
        $this->addSql('DROP INDEX IDX_140AB620727ACA70 ON page');
        $this->addSql('ALTER TABLE page DROP parent_id, DROP menu_type, DROP level');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page ADD parent_id INT DEFAULT NULL, ADD menu_type SMALLINT NOT NULL, ADD level INT NOT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620727ACA70 FOREIGN KEY (parent_id) REFERENCES page (id)');
        $this->addSql('CREATE INDEX IDX_140AB620727ACA70 ON page (parent_id)');
    }
}
