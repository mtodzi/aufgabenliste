<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520171152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_departments (user_id INT NOT NULL, department_id INT NOT NULL, PRIMARY KEY (user_id, department_id))');
        $this->addSql('CREATE INDEX IDX_BFB57141A76ED395 ON user_departments (user_id)');
        $this->addSql('CREATE INDEX IDX_BFB57141AE80F5DF ON user_departments (department_id)');
        $this->addSql('ALTER TABLE user_departments ADD CONSTRAINT FK_BFB57141A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_departments ADD CONSTRAINT FK_BFB57141AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE department_employee_roles DROP CONSTRAINT fk_4436d7bd60322ac');
        $this->addSql('ALTER TABLE department_employee_roles DROP CONSTRAINT fk_4436d7bae80f5df');
        $this->addSql('DROP TABLE department_employee_roles');
        $this->addSql('ALTER TABLE department DROP CONSTRAINT fk_cd1de18a32c8a3de');
        $this->addSql('DROP INDEX idx_cd1de18a32c8a3de');
        $this->addSql('ALTER TABLE department DROP description');
        $this->addSql('ALTER TABLE department DROP organization_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE department_employee_roles (department_id INT NOT NULL, role_id INT NOT NULL, PRIMARY KEY (department_id, role_id))');
        $this->addSql('CREATE INDEX idx_4436d7bd60322ac ON department_employee_roles (role_id)');
        $this->addSql('CREATE INDEX idx_4436d7bae80f5df ON department_employee_roles (department_id)');
        $this->addSql('ALTER TABLE department_employee_roles ADD CONSTRAINT fk_4436d7bd60322ac FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE department_employee_roles ADD CONSTRAINT fk_4436d7bae80f5df FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_departments DROP CONSTRAINT FK_BFB57141A76ED395');
        $this->addSql('ALTER TABLE user_departments DROP CONSTRAINT FK_BFB57141AE80F5DF');
        $this->addSql('DROP TABLE user_departments');
        $this->addSql('ALTER TABLE department ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE department ADD organization_id INT NOT NULL');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT fk_cd1de18a32c8a3de FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_cd1de18a32c8a3de ON department (organization_id)');
    }
}
