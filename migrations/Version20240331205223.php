<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240331205223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE page_url (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, position INT NOT NULL, parent_id INT NOT NULL, INDEX IDX_38C94D3FC4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page_url ADD CONSTRAINT FK_38C94D3FC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('DROP INDEX UNIQ_140AB620989D9B62 ON page');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_url DROP FOREIGN KEY FK_38C94D3FC4663E4');
        $this->addSql('DROP TABLE page_url');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620989D9B62 ON page (slug)');
    }
}
