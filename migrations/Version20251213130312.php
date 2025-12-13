<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251213130312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE instance (id INT AUTO_INCREMENT NOT NULL, slug BINARY(16) NOT NULL, nom VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, sigle VARCHAR(72) DEFAULT NULL, instance_parent_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_4230B1DE989D9B62 (slug), INDEX IDX_4230B1DE77783764 (instance_parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE organe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, role VARCHAR(20) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, connexion INT DEFAULT NULL, last_connected_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, actif TINYINT DEFAULT NULL, created_at DATETIME DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, userpass VARCHAR(255) DEFAULT NULL, nom VARCHAR(72) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, user_id INT DEFAULT NULL, organe_id INT DEFAULT NULL, instance_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B3A76ED395 (user_id), INDEX IDX_1D1C63B3B5E5B09D (organe_id), INDEX IDX_1D1C63B33A51721D (instance_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE instance ADD CONSTRAINT FK_4230B1DE77783764 FOREIGN KEY (instance_parent_id) REFERENCES instance (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3B5E5B09D FOREIGN KEY (organe_id) REFERENCES organe (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B33A51721D FOREIGN KEY (instance_id) REFERENCES instance (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE instance DROP FOREIGN KEY FK_4230B1DE77783764');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3A76ED395');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3B5E5B09D');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B33A51721D');
        $this->addSql('DROP TABLE instance');
        $this->addSql('DROP TABLE organe');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
