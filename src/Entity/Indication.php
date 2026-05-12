<?php

namespace App\Entity;

use App\Repository\IndicationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IndicationRepository::class)]
class Indication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Rendez-vous au cours duquel la prescription a été établie
    // (la date de prescription = date du RDV, donc pas stockée ici)
    #[ORM\ManyToOne(inversedBy: 'lesIndications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RendezVous $rendezVous = null;

    // Médicament prescrit (plusieurs indications peuvent référencer le même médicament)
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medicament $medicament = null;

    // Quantité par prise (ex: 1 comprimé, 2 gélules...)
    #[ORM\Column]
    private ?int $quantite = null;

    // Durée du traitement, exprimée en jours
    #[ORM\Column]
    private ?int $duree = null;

    // Nombre de prises par jour (posologie journalière)
    #[ORM\Column]
    private ?int $nbPriseParJour = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRendezVous(): ?RendezVous
    {
        return $this->rendezVous;
    }

    public function setRendezVous(?RendezVous $rendezVous): static
    {
        $this->rendezVous = $rendezVous;

        return $this;
    }

    public function getMedicament(): ?Medicament
    {
        return $this->medicament;
    }

    public function setMedicament(?Medicament $medicament): static
    {
        $this->medicament = $medicament;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getNbPriseParJour(): ?int
    {
        return $this->nbPriseParJour;
    }

    public function setNbPriseParJour(int $nbPriseParJour): static
    {
        $this->nbPriseParJour = $nbPriseParJour;

        return $this;
    }
}
