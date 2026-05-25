<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260522132637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_members ADD role_id INT NOT NULL');
        $this->addSql('ALTER TABLE project_members DROP role');
        $this->addSql('ALTER TABLE project_members ADD CONSTRAINT FK_D3BEDE9AD60322AC FOREIGN KEY (role_id) REFERENCES project_roles (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_D3BEDE9AD60322AC ON project_members (role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_members DROP CONSTRAINT FK_D3BEDE9AD60322AC');
        $this->addSql('DROP INDEX IDX_D3BEDE9AD60322AC');
        $this->addSql('ALTER TABLE project_members ADD role VARCHAR(50) DEFAULT \'member\' NOT NULL');
        $this->addSql('ALTER TABLE project_members DROP role_id');
    }
}
