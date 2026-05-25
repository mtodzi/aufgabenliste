<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260521160452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_statuses ADD organization_id INT NOT NULL');
        $this->addSql('ALTER TABLE project_statuses ADD CONSTRAINT FK_76BE95CA32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_76BE95CA32C8A3DE ON project_statuses (organization_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_statuses DROP CONSTRAINT FK_76BE95CA32C8A3DE');
        $this->addSql('DROP INDEX IDX_76BE95CA32C8A3DE');
        $this->addSql('ALTER TABLE project_statuses DROP organization_id');
    }
}
