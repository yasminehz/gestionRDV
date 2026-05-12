<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Entity\Medicament;
use App\Repository\MedicamentRepository;
use App\Repository\PatientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MedicamentController extends AbstractController
{
    /**
     * Vue assistant : pour un médicament choisi dans la liste déroulante,
     * affiche la liste DISTINCTE des patients à qui il a été prescrit par
     * le médecin auquel l'assistant est rattaché.
     */
    #[Route('/medicament/patients', name: 'app_medicament_patients', methods: ['GET'])]
    public function patientsByMedicament(
        Request $request,
        MedicamentRepository $medicamentRepository,
        PatientRepository $patientRepository
    ): Response {
        $user = $this->getUser();

        // Sécurité : la fonctionnalité est réservée aux assistants.
        // (Un médecin a déjà sa liste de RDV et un patient ne doit pas voir les autres patients.)
        if (!$this->isGranted('ROLE_ASSISTANT') || !$user instanceof Assistant) {
            throw $this->createAccessDeniedException('Cette page est réservée aux assistants.');
        }

        // Tous les médicaments servent à alimenter la liste déroulante
        $medicaments = $medicamentRepository->findBy([], ['libelle' => 'ASC']);

        // Médicament sélectionné via le paramètre GET ?medicament=ID (vide au 1er affichage)
        $medicamentSelectionne = null;
        $patients = [];

        $medicamentId = $request->query->get('medicament');
        if ($medicamentId !== null && $medicamentId !== '') {
            $medicamentSelectionne = $medicamentRepository->find((int) $medicamentId);

            if ($medicamentSelectionne instanceof Medicament) {
                // On filtre uniquement sur les RDV du médecin de l'assistant connecté
                $patients = $patientRepository->findByMedicamentAndMedecin(
                    $medicamentSelectionne,
                    $user->getMedecin()
                );
            }
        }

        return $this->render('medicament/patients.html.twig', [
            'medicaments'           => $medicaments,
            'medicamentSelectionne' => $medicamentSelectionne,
            'patients'              => $patients,
        ]);
    }
}
