<?php

namespace App\Entity;

use App\Repository\AssistantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssistantRepository::class)]
class Assistant extends User
{
    #[ORM\ManyToOne(inversedBy: 'assistants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medecin $medecin = null;

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): static
    {
        $this->medecin = $medecin;

        return $this;
    }
}
