<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512070932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE indisponibilite DROP FOREIGN KEY FK_8B1BF8F54F31A84');
        $this->addSql('DROP INDEX idx_8b1bf8f54f31a84 ON indisponibilite');
        $this->addSql('CREATE INDEX IDX_8717036F4F31A84 ON indisponibilite (medecin_id)');
        $this->addSql('ALTER TABLE indisponibilite ADD CONSTRAINT FK_8B1BF8F54F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_216EBEDC1CBFB45E');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_216EBEDC96F4DC41');
        $this->addSql('DROP INDEX idx_216ebedc1cbfb45e ON prescription');
        $this->addSql('CREATE INDEX IDX_1FBFB8D991EF7EAA ON prescription (rendez_vous_id)');
        $this->addSql('DROP INDEX idx_216ebedc96f4dc41 ON prescription');
        $this->addSql('CREATE INDEX IDX_1FBFB8D9AB0D61F7 ON prescription (medicament_id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_216EBEDC1CBFB45E FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_216EBEDC96F4DC41 FOREIGN KEY (medicament_id) REFERENCES medicament (id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin` COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE indisponibilite DROP FOREIGN KEY FK_8717036F4F31A84');
        $this->addSql('DROP INDEX idx_8717036f4f31a84 ON indisponibilite');
        $this->addSql('CREATE INDEX IDX_8B1BF8F54F31A84 ON indisponibilite (medecin_id)');
        $this->addSql('ALTER TABLE indisponibilite ADD CONSTRAINT FK_8717036F4F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D991EF7EAA');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D9AB0D61F7');
        $this->addSql('DROP INDEX idx_1fbfb8d991ef7eaa ON prescription');
        $this->addSql('CREATE INDEX IDX_216EBEDC1CBFB45E ON prescription (rendez_vous_id)');
        $this->addSql('DROP INDEX idx_1fbfb8d9ab0d61f7 ON prescription');
        $this->addSql('CREATE INDEX IDX_216EBEDC96F4DC41 ON prescription (medicament_id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D991EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D9AB0D61F7 FOREIGN KEY (medicament_id) REFERENCES medicament (id)');
    }
}
