<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210175033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, actif TINYINT DEFAULT NULL, created_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, organe_id INT DEFAULT NULL, instance_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B3A76ED395 (user_id), INDEX IDX_1D1C63B3B5E5B09D (organe_id), INDEX IDX_1D1C63B33A51721D (instance_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3B5E5B09D FOREIGN KEY (organe_id) REFERENCES organe (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B33A51721D FOREIGN KEY (instance_id) REFERENCES instance (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3A76ED395');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3B5E5B09D');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B33A51721D');
        $this->addSql('DROP TABLE utilisateur');
    }
}
