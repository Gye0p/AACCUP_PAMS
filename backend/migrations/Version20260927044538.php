<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260927044538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aaccup_area (id INT AUTO_INCREMENT NOT NULL, area_number VARCHAR(10) NOT NULL, name VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE accreditation_cycle (id INT AUTO_INCREMENT NOT NULL, program_id INT NOT NULL, academic_year VARCHAR(100) NOT NULL, sar_received_date DATE NOT NULL, compliance_deadline DATE NOT NULL, status VARCHAR(20) NOT NULL, INDEX IDX_9E768F483EB8070A (program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE area_assignment (id INT AUTO_INCREMENT NOT NULL, cycle_id INT NOT NULL, area_id INT NOT NULL, ia_status VARCHAR(30) NOT NULL, ia_comments LONGTEXT DEFAULT NULL, INDEX IDX_BF35A6005EC1162 (cycle_id), INDEX IDX_BF35A600BD0F409C (area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE college (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(200) NOT NULL, code VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compliance_activity (id INT AUTO_INCREMENT NOT NULL, area_assignment_id INT NOT NULL, title VARCHAR(300) NOT NULL, description LONGTEXT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, state VARCHAR(30) NOT NULL, ia_comments LONGTEXT DEFAULT NULL, INDEX IDX_1058B9FDC8AF1AC3 (area_assignment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evidence (id INT AUTO_INCREMENT NOT NULL, activity_id INT NOT NULL, uploaded_by_id INT DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, file_size INT DEFAULT NULL, mime_type VARCHAR(100) DEFAULT NULL, original_name VARCHAR(300) DEFAULT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C61571081C06096 (activity_id), INDEX IDX_C615710A2B28FE8 (uploaded_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE internal_accreditor_assignment (id INT AUTO_INCREMENT NOT NULL, internal_accreditor_id INT NOT NULL, area_assignment_id INT NOT NULL, INDEX IDX_605D886B7E3FE2E (internal_accreditor_id), INDEX IDX_605D886BC8AF1AC3 (area_assignment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE monitoring_report (id INT AUTO_INCREMENT NOT NULL, cycle_id INT NOT NULL, generated_by_id INT NOT NULL, generated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', report_type VARCHAR(20) NOT NULL, INDEX IDX_D7115FED5EC1162 (cycle_id), INDEX IDX_D7115FED1BDD81B (generated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE program (id INT AUTO_INCREMENT NOT NULL, college_id INT NOT NULL, name VARCHAR(200) NOT NULL, code VARCHAR(20) NOT NULL, accreditation_level VARCHAR(20) DEFAULT NULL, validity_end DATE DEFAULT NULL, INDEX IDX_92ED7784770124B2 (college_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sar_recommendation (id INT AUTO_INCREMENT NOT NULL, area_assignment_id INT NOT NULL, recommendation_text LONGTEXT NOT NULL, priority VARCHAR(20) NOT NULL, INDEX IDX_A0E8F6EAC8AF1AC3 (area_assignment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, college_id INT DEFAULT NULL, program_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, role VARCHAR(50) NOT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX IDX_1483A5E9770124B2 (college_id), INDEX IDX_1483A5E93EB8070A (program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE accreditation_cycle ADD CONSTRAINT FK_9E768F483EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
        $this->addSql('ALTER TABLE area_assignment ADD CONSTRAINT FK_BF35A6005EC1162 FOREIGN KEY (cycle_id) REFERENCES accreditation_cycle (id)');
        $this->addSql('ALTER TABLE area_assignment ADD CONSTRAINT FK_BF35A600BD0F409C FOREIGN KEY (area_id) REFERENCES aaccup_area (id)');
        $this->addSql('ALTER TABLE compliance_activity ADD CONSTRAINT FK_1058B9FDC8AF1AC3 FOREIGN KEY (area_assignment_id) REFERENCES area_assignment (id)');
        $this->addSql('ALTER TABLE evidence ADD CONSTRAINT FK_C61571081C06096 FOREIGN KEY (activity_id) REFERENCES compliance_activity (id)');
        $this->addSql('ALTER TABLE evidence ADD CONSTRAINT FK_C615710A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE internal_accreditor_assignment ADD CONSTRAINT FK_605D886B7E3FE2E FOREIGN KEY (internal_accreditor_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE internal_accreditor_assignment ADD CONSTRAINT FK_605D886BC8AF1AC3 FOREIGN KEY (area_assignment_id) REFERENCES area_assignment (id)');
        $this->addSql('ALTER TABLE monitoring_report ADD CONSTRAINT FK_D7115FED5EC1162 FOREIGN KEY (cycle_id) REFERENCES accreditation_cycle (id)');
        $this->addSql('ALTER TABLE monitoring_report ADD CONSTRAINT FK_D7115FED1BDD81B FOREIGN KEY (generated_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE program ADD CONSTRAINT FK_92ED7784770124B2 FOREIGN KEY (college_id) REFERENCES college (id)');
        $this->addSql('ALTER TABLE sar_recommendation ADD CONSTRAINT FK_A0E8F6EAC8AF1AC3 FOREIGN KEY (area_assignment_id) REFERENCES area_assignment (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9770124B2 FOREIGN KEY (college_id) REFERENCES college (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E93EB8070A FOREIGN KEY (program_id) REFERENCES program (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE accreditation_cycle DROP FOREIGN KEY FK_9E768F483EB8070A');
        $this->addSql('ALTER TABLE area_assignment DROP FOREIGN KEY FK_BF35A6005EC1162');
        $this->addSql('ALTER TABLE area_assignment DROP FOREIGN KEY FK_BF35A600BD0F409C');
        $this->addSql('ALTER TABLE compliance_activity DROP FOREIGN KEY FK_1058B9FDC8AF1AC3');
        $this->addSql('ALTER TABLE evidence DROP FOREIGN KEY FK_C61571081C06096');
        $this->addSql('ALTER TABLE evidence DROP FOREIGN KEY FK_C615710A2B28FE8');
        $this->addSql('ALTER TABLE internal_accreditor_assignment DROP FOREIGN KEY FK_605D886B7E3FE2E');
        $this->addSql('ALTER TABLE internal_accreditor_assignment DROP FOREIGN KEY FK_605D886BC8AF1AC3');
        $this->addSql('ALTER TABLE monitoring_report DROP FOREIGN KEY FK_D7115FED5EC1162');
        $this->addSql('ALTER TABLE monitoring_report DROP FOREIGN KEY FK_D7115FED1BDD81B');
        $this->addSql('ALTER TABLE program DROP FOREIGN KEY FK_92ED7784770124B2');
        $this->addSql('ALTER TABLE sar_recommendation DROP FOREIGN KEY FK_A0E8F6EAC8AF1AC3');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9770124B2');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E93EB8070A');
        $this->addSql('DROP TABLE aaccup_area');
        $this->addSql('DROP TABLE accreditation_cycle');
        $this->addSql('DROP TABLE area_assignment');
        $this->addSql('DROP TABLE college');
        $this->addSql('DROP TABLE compliance_activity');
        $this->addSql('DROP TABLE evidence');
        $this->addSql('DROP TABLE internal_accreditor_assignment');
        $this->addSql('DROP TABLE monitoring_report');
        $this->addSql('DROP TABLE program');
        $this->addSql('DROP TABLE sar_recommendation');
        $this->addSql('DROP TABLE users');
    }
}
