<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260512070216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Medicament and Prescription tables with relationships to RendezVous';
    }

    public function up(Schema $schema): void
    {
        // Create medicament table
        $this->addSql('CREATE TABLE medicament (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create prescription table
        $this->addSql('CREATE TABLE prescription (id INT AUTO_INCREMENT NOT NULL, rendez_vous_id INT NOT NULL, medicament_id INT NOT NULL, quantite INT NOT NULL, nombre_prise INT NOT NULL, duree TIME NOT NULL, INDEX IDX_216EBEDC1CBFB45E (rendez_vous_id), INDEX IDX_216EBEDC96F4DC41 (medicament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign keys
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_216EBEDC1CBFB45E FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_216EBEDC96F4DC41 FOREIGN KEY (medicament_id) REFERENCES medicament (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign keys
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_216EBEDC1CBFB45E');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_216EBEDC96F4DC41');

        // Drop tables
        $this->addSql('DROP TABLE prescription');
        $this->addSql('DROP TABLE medicament');
    }
}
