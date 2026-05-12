<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajout de la prescription :
 *  - table `medicament` (libelle)
 *  - table `indication` (RDV -> médicament + posologie : quantité, durée, nb prises/jour)
 */
final class Version20260512073236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des tables medicament et indication pour la prescription';
    }

    public function up(Schema $schema): void
    {
        // Table indication : une ligne de prescription d'un médicament pour un RDV
        $this->addSql('CREATE TABLE indication (id INT AUTO_INCREMENT NOT NULL, rendez_vous_id INT NOT NULL, medicament_id INT NOT NULL, quantite INT NOT NULL, duree INT NOT NULL, nb_prise_par_jour INT NOT NULL, INDEX IDX_D15065D791EF7EAA (rendez_vous_id), INDEX IDX_D15065D7AB0D61F7 (medicament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Table medicament : référentiel des médicaments prescriptibles
        $this->addSql('CREATE TABLE medicament (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Clés étrangères : indication -> rendez_vous et indication -> medicament
        $this->addSql('ALTER TABLE indication ADD CONSTRAINT FK_D15065D791EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)');
        $this->addSql('ALTER TABLE indication ADD CONSTRAINT FK_D15065D7AB0D61F7 FOREIGN KEY (medicament_id) REFERENCES medicament (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE indication DROP FOREIGN KEY FK_D15065D791EF7EAA');
        $this->addSql('ALTER TABLE indication DROP FOREIGN KEY FK_D15065D7AB0D61F7');
        $this->addSql('DROP TABLE indication');
        $this->addSql('DROP TABLE medicament');
    }
}
