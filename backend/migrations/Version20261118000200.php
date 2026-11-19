<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261118000200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add accreditation level and Level IV area labels';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE accreditation_cycle
            ADD accreditation_level VARCHAR(20) NOT NULL DEFAULT \'Level I\',
            ADD program_type VARCHAR(20) DEFAULT NULL');

        $this->addSql("INSERT INTO aaccup_area (area_number, name)
            SELECT 'IV-1', 'Research'
            WHERE NOT EXISTS (SELECT 1 FROM aaccup_area WHERE area_number = 'IV-1')");
        $this->addSql("INSERT INTO aaccup_area (area_number, name)
            SELECT 'IV-2', 'Performance of Graduates'
            WHERE NOT EXISTS (SELECT 1 FROM aaccup_area WHERE area_number = 'IV-2')");
        $this->addSql("INSERT INTO aaccup_area (area_number, name)
            SELECT 'IV-3', 'Community Service'
            WHERE NOT EXISTS (SELECT 1 FROM aaccup_area WHERE area_number = 'IV-3')");
        $this->addSql("INSERT INTO aaccup_area (area_number, name)
            SELECT 'IV-4', 'International Linkages and Consortia'
            WHERE NOT EXISTS (SELECT 1 FROM aaccup_area WHERE area_number = 'IV-4')");
        $this->addSql("INSERT INTO aaccup_area (area_number, name)
            SELECT 'IV-5', 'Planning'
            WHERE NOT EXISTS (SELECT 1 FROM aaccup_area WHERE area_number = 'IV-5')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM aaccup_area
            WHERE area_number IN ('IV-1', 'IV-2', 'IV-3', 'IV-4', 'IV-5')");
        $this->addSql('ALTER TABLE accreditation_cycle
            DROP accreditation_level,
            DROP program_type');
    }
}
