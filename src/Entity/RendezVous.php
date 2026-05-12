<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lesRendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(inversedBy: 'lesRendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medecin $medecin = null;

    #[ORM\Column]
    private ?\DateTime $debut = null;

    #[ORM\Column]
    private ?\DateTime $fin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    // Prescription : un RDV peut générer plusieurs indications (lignes de prescription).
    // orphanRemoval => si on retire une indication du RDV, elle est supprimée en BDD.
    /**
     * @var Collection<int, Indication>
     */
    #[ORM\OneToMany(targetEntity: Indication::class, mappedBy: 'rendezVous', orphanRemoval: true, cascade: ['persist'])]
    private Collection $lesIndications;

    public function __construct()
    {
        $this->lesIndications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): static
    {
        $this->medecin = $medecin;

        return $this;
    }

    public function getDebut(): ?\DateTime
    {
        return $this->debut;
    }

    public function setDebut(?\DateTime $debut): static
    {
        $this->debut = $debut;

        return $this;
    }

    public function getFin(): ?\DateTime
    {
        return $this->fin;
    }

    public function setFin(?\DateTime $fin): static
    {
        $this->fin = $fin;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, Indication>
     */
    public function getLesIndications(): Collection
    {
        return $this->lesIndications;
    }

    public function addLesIndication(Indication $indication): static
    {
        if (!$this->lesIndications->contains($indication)) {
            $this->lesIndications->add($indication);
            // Maintien de la cohérence des deux côtés de la relation
            $indication->setRendezVous($this);
        }

        return $this;
    }

    public function removeLesIndication(Indication $indication): static
    {
        if ($this->lesIndications->removeElement($indication)) {
            // Côté propriétaire : on détache le RDV (orphanRemoval déclenchera la suppression)
            if ($indication->getRendezVous() === $this) {
                $indication->setRendezVous(null);
            }
        }

        return $this;
    }
}
