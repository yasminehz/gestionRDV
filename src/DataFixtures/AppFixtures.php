<?php

namespace App\DataFixtures;

use App\Entity\Assistant;
use App\Entity\RendezVous;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\Etat;
use App\Repository\EtatRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EtatRepository $etatRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer des médecins
        $medecins = [];
        $medecinData = [
            ['email' => 'dr.wartel@clinic.fr', 'nom' => 'Wartel', 'prenom' => 'Marius'],
            ['email' => 'dr.zitoun@clinic.fr', 'nom' => 'Zitoun', 'prenom' => 'Wassim'],
            ['email' => 'dr.henni@clinic.fr', 'nom' => 'Henni', 'prenom' => 'Yasmine'],
        ];

        foreach ($medecinData as $data) {
            $medecin = new Medecin();
            $medecin->setEmail($data['email']);
            $medecin->setNom($data['nom']);
            $medecin->setPrenom($data['prenom']);
            $medecin->setRoles(['ROLE_MEDECIN']);
            
            $hashedPassword = $this->passwordHasher->hashPassword($medecin, 'password123');
            $medecin->setPassword($hashedPassword);
            
            $manager->persist($medecin);
            $medecins[] = $medecin;
        }

        // Créer des assistants associés aux médecins
        $assistants = [];
        $assistantData = [
            ['email' => 'assistant.sophie@clinic.fr', 'nom' => 'Moreau', 'prenom' => 'Sophie', 'medecin_index' => 0],
            ['email' => 'assistant.luc@clinic.fr', 'nom' => 'Lefevre', 'prenom' => 'Luc', 'medecin_index' => 0],
            ['email' => 'assistant.isabelle@clinic.fr', 'nom' => 'Richard', 'prenom' => 'Isabelle', 'medecin_index' => 1],
            ['email' => 'assistant.thomas@clinic.fr', 'nom' => 'Petit', 'prenom' => 'Thomas', 'medecin_index' => 2],
        ];

        foreach ($assistantData as $data) {
            $assistant = new Assistant();
            $assistant->setEmail($data['email']);
            $assistant->setNom($data['nom']);
            $assistant->setPrenom($data['prenom']);
            $assistant->setRoles(['ROLE_ASSISTANT']);
            $assistant->setMedecin($medecins[$data['medecin_index']]);
            
            $hashedPassword = $this->passwordHasher->hashPassword($assistant, 'password123');
            $assistant->setPassword($hashedPassword);
            
            $manager->persist($assistant);
            $assistants[] = $assistant;
        }

        // Créer des patients
        $patients = [];
        $patientData = [
            ['email' => 'jean.patient@mail.fr', 'nom' => 'Patient', 'prenom' => 'Jean'],
            ['email' => 'marie.patient@mail.fr', 'nom' => 'Dupuis', 'prenom' => 'Marie'],
            ['email' => 'marc.patient@mail.fr', 'nom' => 'Leclerc', 'prenom' => 'Marc'],
            ['email' => 'anne.patient@mail.fr', 'nom' => 'Moreau', 'prenom' => 'Anne'],
            ['email' => 'paul.patient@mail.fr', 'nom' => 'Fournier', 'prenom' => 'Paul'],
            ['email' => 'claire.patient@mail.fr', 'nom' => 'Girard', 'prenom' => 'Claire'],
        ];

        foreach ($patientData as $data) {
            $patient = new Patient();
            $patient->setEmail($data['email']);
            $patient->setNom($data['nom']);
            $patient->setPrenom($data['prenom']);
            $patient->setRoles(['ROLE_PATIENT']);
            
            $hashedPassword = $this->passwordHasher->hashPassword($patient, 'password123');
            $patient->setPassword($hashedPassword);
            
            $manager->persist($patient);
            $patients[] = $patient;
        }

        $manager->flush();

        // Créer des rendez-vous après que les entités soient persistées
        $rendezvousData = [
            // Rendez-vous du patient 0 avec médecin 0
            [
                'patient_index' => 0,
                'medecin_index' => 0,
                'datetime' => (new \DateTime())->add(new \DateInterval('P1D')),
                'etat' => 'demande',
            ],
            [
                'patient_index' => 0,
                'medecin_index' => 0,
                'datetime' => (new \DateTime())->add(new \DateInterval('P3D')),
                'etat' => 'confirme',
            ],
            // Rendez-vous du patient 1 avec médecin 0
            [
                'patient_index' => 1,
                'medecin_index' => 0,
                'datetime' => (new \DateTime())->add(new \DateInterval('P2D')),
                'etat' => 'demande',
            ],
            // Rendez-vous du patient 2 avec médecin 1
            [
                'patient_index' => 2,
                'medecin_index' => 1,
                'datetime' => (new \DateTime())->add(new \DateInterval('P5D')),
                'etat' => 'confirme',
            ],
            [
                'patient_index' => 2,
                'medecin_index' => 1,
                'datetime' => (new \DateTime())->add(new \DateInterval('P7D')),
                'etat' => 'realise',
            ],
            // Rendez-vous du patient 3 avec médecin 2
            [
                'patient_index' => 3,
                'medecin_index' => 2,
                'datetime' => (new \DateTime())->add(new \DateInterval('P4D')),
                'etat' => 'refuse',
            ],
            // Rendez-vous du patient 4 avec médecin 0
            [
                'patient_index' => 4,
                'medecin_index' => 0,
                'datetime' => (new \DateTime())->add(new \DateInterval('P6D')),
                'etat' => 'confirme',
            ],
            // Rendez-vous du patient 5 avec médecin 1
            [
                'patient_index' => 5,
                'medecin_index' => 1,
                'datetime' => (new \DateTime())->add(new \DateInterval('P2D')),
                'etat' => 'demande',
            ],
            [
                'patient_index' => 5,
                'medecin_index' => 1,
                'datetime' => (new \DateTime())->add(new \DateInterval('P8D')),
                'etat' => 'annule',
            ],
        ];

        foreach ($rendezvousData as $data) {
            $rv = new RendezVous();
            $rv->setPatient($patients[$data['patient_index']]);
            $rv->setMedecin($medecins[$data['medecin_index']]);
            
            $start = $data['datetime'];
            $end = (clone $start)->add(new \DateInterval('PT30M'));
            $rv->setDebut($start);
            $rv->setFin($end);
            
            // Récupérer l'entité Etat via le repository
            $etat = $this->etatRepository->findOneBy(['libelle' => $data['etat']]);
            $rv->setEtat($etat);

            $manager->persist($rv);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EtatFixtures::class,
        ];
    }
}
