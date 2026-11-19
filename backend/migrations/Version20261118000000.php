<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * QUAMC PAMS changes (November 2026):
 *   - Area Coordinator role: new area_coordinator_assignment table
 *   - SarRecommendation: add type, compliance_documents, remarks columns
 *   - Evidence: activity_id becomes nullable, add area_assignment_id FK
 *   - AccreditationCycle: add optional_area_ids JSON column
 *   - Notification: new table for in-app notifications
 */
final class Version20261118000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'QUAMC November 2026 changes: Area Coordinator, SAR compliance table, area-level evidence, optional areas, in-app notifications';
    }

    public function up(Schema $schema): void
    {
        // 1. Area Coordinator Assignment table
        $this->addSql('CREATE TABLE area_coordinator_assignment (
            id INT AUTO_INCREMENT NOT NULL,
            area_coordinator_id INT NOT NULL,
            area_assignment_id INT NOT NULL,
            INDEX IDX_ACA_COORDINATOR (area_coordinator_id),
            INDEX IDX_ACA_AREA (area_assignment_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE area_coordinator_assignment
            ADD CONSTRAINT FK_ACA_COORDINATOR FOREIGN KEY (area_coordinator_id) REFERENCES users (id),
            ADD CONSTRAINT FK_ACA_AREA FOREIGN KEY (area_assignment_id) REFERENCES area_assignment (id)');

        // 2. SAR Recommendation: add type, compliance_documents, remarks
        $this->addSql("ALTER TABLE sar_recommendation
            ADD type VARCHAR(20) NOT NULL DEFAULT 'mandatory',
            ADD compliance_documents LONGTEXT DEFAULT NULL,
            ADD remarks VARCHAR(20) DEFAULT NULL");

        // 3. Evidence: make activity_id nullable, add area_assignment_id
        $this->addSql('ALTER TABLE evidence
            DROP FOREIGN KEY FK_C61571081C06096');
        $this->addSql('ALTER TABLE evidence
            MODIFY activity_id INT DEFAULT NULL,
            ADD area_assignment_id INT DEFAULT NULL,
            ADD INDEX IDX_EVIDENCE_AA (area_assignment_id)');
        $this->addSql('ALTER TABLE evidence
            ADD CONSTRAINT FK_C61571081C06096 FOREIGN KEY (activity_id) REFERENCES compliance_activity (id),
            ADD CONSTRAINT FK_EVIDENCE_AA FOREIGN KEY (area_assignment_id) REFERENCES area_assignment (id)');

        // 4. AccreditationCycle: add optional_area_ids JSON column
        $this->addSql('ALTER TABLE accreditation_cycle
            ADD optional_area_ids JSON DEFAULT NULL');

        // 5. Notification table
        $this->addSql('CREATE TABLE notification (
            id INT AUTO_INCREMENT NOT NULL,
            recipient_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            message LONGTEXT NOT NULL,
            link VARCHAR(300) DEFAULT NULL,
            read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_NOTIF_RECIPIENT (recipient_id),
            INDEX IDX_NOTIF_READ (read_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE notification
            ADD CONSTRAINT FK_NOTIF_RECIPIENT FOREIGN KEY (recipient_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // Notifications
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_NOTIF_RECIPIENT');
        $this->addSql('DROP TABLE notification');

        // AccreditationCycle optional_area_ids
        $this->addSql('ALTER TABLE accreditation_cycle DROP COLUMN optional_area_ids');

        // Evidence revert
        $this->addSql('ALTER TABLE evidence DROP FOREIGN KEY FK_C61571081C06096');
        $this->addSql('ALTER TABLE evidence DROP FOREIGN KEY FK_EVIDENCE_AA');
        $this->addSql('ALTER TABLE evidence DROP INDEX IDX_EVIDENCE_AA');
        $this->addSql('ALTER TABLE evidence DROP COLUMN area_assignment_id');
        $this->addSql('ALTER TABLE evidence MODIFY activity_id INT NOT NULL');
        $this->addSql('ALTER TABLE evidence ADD CONSTRAINT FK_C61571081C06096 FOREIGN KEY (activity_id) REFERENCES compliance_activity (id)');

        // SAR Recommendation revert
        $this->addSql('ALTER TABLE sar_recommendation DROP COLUMN type, DROP COLUMN compliance_documents, DROP COLUMN remarks');

        // Area Coordinator Assignment
        $this->addSql('ALTER TABLE area_coordinator_assignment DROP FOREIGN KEY FK_ACA_COORDINATOR');
        $this->addSql('ALTER TABLE area_coordinator_assignment DROP FOREIGN KEY FK_ACA_AREA');
        $this->addSql('DROP TABLE area_coordinator_assignment');
    }
}
