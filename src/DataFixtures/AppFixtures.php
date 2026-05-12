<?php

namespace App\DataFixtures;

use App\Entity\Assistant;
use App\Entity\Indication;
use App\Entity\Medicament;
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

        // ====================================================================
        // RENDEZ-VOUS
        // Les libellés d'état doivent matcher EXACTEMENT ceux d'EtatFixtures
        // (avec accents : 'demandé', 'confirmé', 'annulé', 'refusé', 'réalisé')
        // ====================================================================
        $rendezvousData = [
            // RDV 0 : Jean ↔ Dr Wartel (médecin 0) - demande à venir
            ['patient_index' => 0, 'medecin_index' => 0, 'datetime' => (new \DateTime())->add(new \DateInterval('P1D')),  'etat' => 'demandé'],
            // RDV 1 : Jean ↔ Dr Wartel - confirmé (futur, recevra des prescriptions)
            ['patient_index' => 0, 'medecin_index' => 0, 'datetime' => (new \DateTime())->add(new \DateInterval('P3D')),  'etat' => 'confirmé'],
            // RDV 2 : Marie ↔ Dr Wartel - demandé
            ['patient_index' => 1, 'medecin_index' => 0, 'datetime' => (new \DateTime())->add(new \DateInterval('P2D')),  'etat' => 'demandé'],
            // RDV 3 : Marc ↔ Dr Zitoun - confirmé (recevra prescription)
            ['patient_index' => 2, 'medecin_index' => 1, 'datetime' => (new \DateTime())->add(new \DateInterval('P5D')),  'etat' => 'confirmé'],
            // RDV 4 : Marc ↔ Dr Zitoun - réalisé (a déjà reçu prescription)
            ['patient_index' => 2, 'medecin_index' => 1, 'datetime' => (new \DateTime())->sub(new \DateInterval('P7D')),  'etat' => 'réalisé'],
            // RDV 5 : Anne ↔ Dr Henni - refusé
            ['patient_index' => 3, 'medecin_index' => 2, 'datetime' => (new \DateTime())->add(new \DateInterval('P4D')),  'etat' => 'refusé'],
            // RDV 6 : Paul ↔ Dr Wartel - confirmé (recevra prescription)
            ['patient_index' => 4, 'medecin_index' => 0, 'datetime' => (new \DateTime())->add(new \DateInterval('P6D')),  'etat' => 'confirmé'],
            // RDV 7 : Claire ↔ Dr Zitoun - demandé
            ['patient_index' => 5, 'medecin_index' => 1, 'datetime' => (new \DateTime())->add(new \DateInterval('P2D')),  'etat' => 'demandé'],
            // RDV 8 : Claire ↔ Dr Zitoun - annulé
            ['patient_index' => 5, 'medecin_index' => 1, 'datetime' => (new \DateTime())->add(new \DateInterval('P8D')),  'etat' => 'annulé'],
            // RDV 9 : Anne ↔ Dr Henni - réalisé (recevra prescription pour donner du contenu au Dr Henni)
            ['patient_index' => 3, 'medecin_index' => 2, 'datetime' => (new \DateTime())->sub(new \DateInterval('P3D')),  'etat' => 'réalisé'],
        ];

        // On garde les RDV créés dans un tableau pour pouvoir y rattacher les indications plus bas
        $rendezVous = [];
        foreach ($rendezvousData as $data) {
            $rv = new RendezVous();
            $rv->setPatient($patients[$data['patient_index']]);
            $rv->setMedecin($medecins[$data['medecin_index']]);

            $start = $data['datetime'];
            $end = (clone $start)->add(new \DateInterval('PT30M'));
            $rv->setDebut($start);
            $rv->setFin($end);

            $etat = $this->etatRepository->findOneBy(['libelle' => $data['etat']]);
            $rv->setEtat($etat);

            $manager->persist($rv);
            $rendezVous[] = $rv;
        }

        // ====================================================================
        // MÉDICAMENTS (5)
        // ====================================================================
        $medicaments = [];
        $medicamentLibelles = [
            'Doliprane 1000mg',
            'Ibuprofène 400mg',
            'Amoxicilline 500mg',
            'Spasfon',
            'Smecta',
        ];
        foreach ($medicamentLibelles as $libelle) {
            $m = new Medicament();
            $m->setLibelle($libelle);
            $manager->persist($m);
            $medicaments[] = $m;
        }

        // ====================================================================
        // INDICATIONS (prescriptions)
        // Plusieurs prescriptions par médecin et par patient, avec recouvrement
        // sur les médicaments pour tester la recherche "patients par médicament".
        //
        // Format : [rdv_index, medicament_index, quantite, nbPriseParJour, duree(jours)]
        // ====================================================================
        $indicationData = [
            // RDV 1 : Jean (Wartel) - Doliprane + Ibuprofène
            [1, 0, 1, 3, 5],   // Doliprane 1×3/j pendant 5j
            [1, 1, 1, 2, 3],   // Ibuprofène 1×2/j pendant 3j

            // RDV 3 : Marc (Zitoun) - Amoxicilline (antibio)
            [3, 2, 1, 3, 7],   // Amoxicilline 1×3/j pendant 7j

            // RDV 4 : Marc (Zitoun) - Doliprane + Spasfon (consultation déjà réalisée)
            [4, 0, 2, 3, 5],   // Doliprane 2×3/j pendant 5j
            [4, 3, 1, 3, 5],   // Spasfon 1×3/j pendant 5j

            // RDV 6 : Paul (Wartel) - Doliprane + Smecta + Ibuprofène
            [6, 0, 1, 4, 7],   // Doliprane 1×4/j pendant 7j
            [6, 4, 1, 3, 5],   // Smecta 1×3/j pendant 5j
            [6, 1, 1, 2, 4],   // Ibuprofène 1×2/j pendant 4j

            // RDV 9 : Anne (Henni) - Spasfon + Doliprane
            [9, 3, 2, 2, 4],   // Spasfon 2×2/j pendant 4j
            [9, 0, 1, 3, 3],   // Doliprane 1×3/j pendant 3j
        ];

        foreach ($indicationData as [$rdvIdx, $medIdx, $qte, $nbPrise, $duree]) {
            $ind = new Indication();
            $ind->setRendezVous($rendezVous[$rdvIdx]);
            $ind->setMedicament($medicaments[$medIdx]);
            $ind->setQuantite($qte);
            $ind->setNbPriseParJour($nbPrise);
            $ind->setDuree($duree);
            $manager->persist($ind);
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
