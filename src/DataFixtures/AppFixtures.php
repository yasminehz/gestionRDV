<?php

namespace App\DataFixtures;

use App\Entity\Assistant;
use App\Entity\Demandes;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Enum\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer des médecins
        $medecins = [];
        $medecinData = [
            ['email' => 'dr.martin@clinic.fr', 'nom' => 'Martin', 'prenom' => 'Jean', 'specialite' => 'Généraliste'],
            ['email' => 'dr.dupont@clinic.fr', 'nom' => 'Dupont', 'prenom' => 'Marie', 'specialite' => 'Cardiologue'],
            ['email' => 'dr.bernard@clinic.fr', 'nom' => 'Bernard', 'prenom' => 'Pierre', 'specialite' => 'Dermatologue'],
        ];

        foreach ($medecinData as $data) {
            $medecin = new Medecin();
            $medecin->setEmail($data['email']);
            $medecin->setNom($data['nom']);
            $medecin->setPrenom($data['prenom']);
            $medecin->setRoles(['ROLE_MEDECIN']);
            
            // Hasher le mot de passe (même pour tous : "password123")
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

        // Créer des demandes après que les entités soient persistées
        $demandesData = [
            // Demandes du patient 0 avec médecin 0
            [
                'patient_index' => 0,
                'medecin_index' => 0,
                'datetime' => (new \DateTime())->add(new \DateInterval('P1D')),
                'etat' => Etat::DEMANDE,
            ],
            [
                'patient_index' => 0,
                'medecin_index' => 0,
                'datetime' => (new \DateTime())->add(new \DateInterval('P3D')),
                'etat' => Etat::CONFIRME,
            ],
            // Demandes du patient 1 avec médecin 0
            [
                'patient_index' => 1,
                'medecin_index' => 0,
                'datetime' => (new \DateTime())->add(new \DateInterval('P2D')),
                'etat' => Etat::DEMANDE,
            ],
            // Demandes du patient 2 avec médecin 1
            [
                'patient_index' => 2,
                'medecin_index' => 1,
                'datetime' => (new \DateTime())->add(new \DateInterval('P5D')),
                'etat' => Etat::CONFIRME,
            ],
            [
                'patient_index' => 2,
                'medecin_index' => 1,
                'datetime' => (new \DateTime())->add(new \DateInterval('P7D')),
                'etat' => Etat::DONE,
            ],
            // Demandes du patient 3 avec médecin 2
            [
                'patient_index' => 3,
                'medecin_index' => 2,
                'datetime' => (new \DateTime())->add(new \DateInterval('P4D')),
                'etat' => Etat::REFUSE,
            ],
            // Demandes du patient 4 avec médecin 0
            [
                'patient_index' => 4,
                'medecin_index' => 0,
                'datetime' => (new \DateTime())->add(new \DateInterval('P6D')),
                'etat' => Etat::CONFIRME,
            ],
            // Demandes du patient 5 avec médecin 1
            [
                'patient_index' => 5,
                'medecin_index' => 1,
                'datetime' => (new \DateTime())->add(new \DateInterval('P2D')),
                'etat' => Etat::DEMANDE,
            ],
            [
                'patient_index' => 5,
                'medecin_index' => 1,
                'datetime' => (new \DateTime())->add(new \DateInterval('P8D')),
                'etat' => Etat::ANNULE,
            ],
        ];

        foreach ($demandesData as $data) {
            $demande = new Demandes();
            $demande->setPatient($patients[$data['patient_index']]);
            $demande->setMedecin($medecins[$data['medecin_index']]);
            $demande->setDatetime($data['datetime']);
            $demande->setEtat($data['etat']);
            
            $manager->persist($demande);
        }

        $manager->flush();
    }
}
