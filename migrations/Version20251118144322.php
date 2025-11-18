<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118144322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demandes (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, medecin_id INT NOT NULL, datetime DATETIME NOT NULL, etat VARCHAR(255) NOT NULL, INDEX IDX_BD940CBB6B899279 (patient_id), INDEX IDX_BD940CBB4F31A84 (medecin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demandes ADD CONSTRAINT FK_BD940CBB6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE demandes ADD CONSTRAINT FK_BD940CBB4F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('ALTER TABLE assistant ADD medecin_id INT NOT NULL');
        $this->addSql('ALTER TABLE assistant ADD CONSTRAINT FK_C2997CD14F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('CREATE INDEX IDX_C2997CD14F31A84 ON assistant (medecin_id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demandes DROP FOREIGN KEY FK_BD940CBB6B899279');
        $this->addSql('ALTER TABLE demandes DROP FOREIGN KEY FK_BD940CBB4F31A84');
        $this->addSql('DROP TABLE demandes');
        $this->addSql('ALTER TABLE assistant DROP FOREIGN KEY FK_C2997CD14F31A84');
        $this->addSql('DROP INDEX IDX_C2997CD14F31A84 ON assistant');
        $this->addSql('ALTER TABLE assistant DROP medecin_id');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin` COMMENT \'(DC2Type:json)\'');
    }
}
