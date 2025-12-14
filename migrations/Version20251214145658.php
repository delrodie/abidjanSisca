<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251214145658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activite (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(32) DEFAULT NULL, slug BINARY(16) DEFAULT NULL, denomination VARCHAR(255) DEFAULT NULL, lieu VARCHAR(255) DEFAULT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, objectif LONGTEXT DEFAULT NULL, contenu LONGTEXT DEFAULT NULL, cible JSON DEFAULT NULL, responsable VARCHAR(255) DEFAULT NULL, partie_prenante JSON DEFAULT NULL, statut VARCHAR(32) DEFAULT NULL, approbation_district_at DATETIME DEFAULT NULL, approbation_region_at DATETIME DEFAULT NULL, niveau_creation VARCHAR(50) DEFAULT NULL, archive TINYINT DEFAULT NULL, commentaire_validation LONGTEXT DEFAULT NULL, motif_rejet LONGTEXT DEFAULT NULL, update_at DATETIME DEFAULT NULL, auteur_id INT DEFAULT NULL, instance_id INT DEFAULT NULL, approbateur_district_id INT DEFAULT NULL, approbateur_region_id INT DEFAULT NULL, INDEX IDX_B875551560BB6FE6 (auteur_id), INDEX IDX_B87555153A51721D (instance_id), INDEX IDX_B87555154D398292 (approbateur_district_id), INDEX IDX_B8755515FA8CF936 (approbateur_region_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B875551560BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B87555153A51721D FOREIGN KEY (instance_id) REFERENCES instance (id)');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B87555154D398292 FOREIGN KEY (approbateur_district_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515FA8CF936 FOREIGN KEY (approbateur_region_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B875551560BB6FE6');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B87555153A51721D');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B87555154D398292');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B8755515FA8CF936');
        $this->addSql('DROP TABLE activite');
    }
}
