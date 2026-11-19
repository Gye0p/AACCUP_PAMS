<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261118000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change Internal Accreditor assignments from individual areas to whole programs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            ADD program_id INT DEFAULT NULL');

        $this->addSql('UPDATE internal_accreditor_assignment ia
            JOIN area_assignment aa ON aa.id = ia.area_assignment_id
            JOIN accreditation_cycle ac ON ac.id = aa.cycle_id
            SET ia.program_id = ac.program_id');

        $this->addSql('DELETE duplicate
            FROM internal_accreditor_assignment duplicate
            JOIN internal_accreditor_assignment keeper
              ON keeper.program_id = duplicate.program_id
             AND keeper.id < duplicate.id');

        $this->addSql('ALTER TABLE internal_accreditor_assignment
            DROP FOREIGN KEY FK_605D886BC8AF1AC3');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            DROP INDEX IDX_605D886BC8AF1AC3');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            DROP COLUMN area_assignment_id');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            MODIFY program_id INT NOT NULL,
            ADD INDEX IDX_IA_PROGRAM (program_id),
            ADD UNIQUE INDEX uniq_internal_accreditor_program (program_id)');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            ADD CONSTRAINT FK_IA_PROGRAM FOREIGN KEY (program_id) REFERENCES program (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            DROP FOREIGN KEY FK_IA_PROGRAM');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            DROP INDEX uniq_internal_accreditor_program');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            DROP INDEX IDX_IA_PROGRAM');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            ADD area_assignment_id INT DEFAULT NULL');

        $this->addSql('INSERT INTO internal_accreditor_assignment
            (internal_accreditor_id, area_assignment_id)
            SELECT ia.internal_accreditor_id, aa.id
            FROM internal_accreditor_assignment ia
            JOIN area_assignment aa ON aa.cycle_id IN (
                SELECT ac.id FROM accreditation_cycle ac WHERE ac.program_id = ia.program_id
            )');

        $this->addSql('DELETE FROM internal_accreditor_assignment
            WHERE area_assignment_id IS NULL');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            MODIFY area_assignment_id INT NOT NULL');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            DROP COLUMN program_id');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            ADD INDEX IDX_605D886BC8AF1AC3 (area_assignment_id)');
        $this->addSql('ALTER TABLE internal_accreditor_assignment
            ADD CONSTRAINT FK_605D886BC8AF1AC3
            FOREIGN KEY (area_assignment_id) REFERENCES area_assignment (id)');
    }
}
