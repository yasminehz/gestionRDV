<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $etats = [
            'demandé',
            'confirmé',
            'annulé',
            'refusé',
            'realisé'
        ];

        foreach ($etats as $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $manager->persist($etat);
            $this->addReference('etat_' . strtolower($libelle), $etat);
        }

        $manager->flush();
    }
}
