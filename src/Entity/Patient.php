<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
class Patient extends User
{
    /**
     * @var Collection<int, Demandes>
     */
    #[ORM\OneToMany(targetEntity: Demandes::class, mappedBy: 'patient', orphanRemoval: true)]
    private Collection $Demandes;

    public function __construct()
    {
        $this->Demandes = new ArrayCollection();
    }

    /**
     * @return Collection<int, Demandes>
     */
    public function getDemandes(): Collection
    {
        return $this->Demandes;
    }

    public function addDemande(Demandes $demande): static
    {
        if (!$this->Demandes->contains($demande)) {
            $this->Demandes->add($demande);
            $demande->setPatient($this);
        }

        return $this;
    }

    public function removeDemande(Demandes $demande): static
    {
        if ($this->Demandes->removeElement($demande)) {
            // set the owning side to null (unless already changed)
            if ($demande->getPatient() === $this) {
                $demande->setPatient(null);
            }
        }

        return $this;
    }
}
