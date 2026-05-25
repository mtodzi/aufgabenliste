<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520182509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE department_role (department_id INT NOT NULL, role_id INT NOT NULL, PRIMARY KEY (department_id, role_id))');
        $this->addSql('CREATE INDEX IDX_F573ED3FAE80F5DF ON department_role (department_id)');
        $this->addSql('CREATE INDEX IDX_F573ED3FD60322AC ON department_role (role_id)');
        $this->addSql('ALTER TABLE department_role ADD CONSTRAINT FK_F573ED3FAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE department_role ADD CONSTRAINT FK_F573ED3FD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE department ADD description TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE department_role DROP CONSTRAINT FK_F573ED3FAE80F5DF');
        $this->addSql('ALTER TABLE department_role DROP CONSTRAINT FK_F573ED3FD60322AC');
        $this->addSql('DROP TABLE department_role');
        $this->addSql('ALTER TABLE department DROP description');
    }
}
