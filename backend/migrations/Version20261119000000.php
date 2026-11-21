<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261119000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add program validity start and audit log';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE program ADD validity_start DATE DEFAULT NULL');
        $this->addSql('CREATE TABLE audit_log (
            id INT AUTO_INCREMENT NOT NULL,
            actor_id INT DEFAULT NULL,
            action VARCHAR(20) NOT NULL,
            entity_type VARCHAR(150) NOT NULL,
            entity_id VARCHAR(100) DEFAULT NULL,
            changes JSON NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_F6E1C0F510DAF24A (actor_id),
            CONSTRAINT FK_AUDIT_ACTOR FOREIGN KEY (actor_id) REFERENCES users (id) ON DELETE SET NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('ALTER TABLE program DROP validity_start');
    }
}
