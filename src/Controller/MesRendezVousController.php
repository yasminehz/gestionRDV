<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Medecin;
use App\Repository\EtatRepository;
use App\Repository\RendezVousRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MesRendezVousController extends AbstractController
{
    #[Route('/mes-rendez-vous', name: 'app_mes_rendez_vous')]
    public function index(
        Request $request,
        RendezVousRepository $rendezVousRepository,
        EtatRepository $etatRepository
    ): Response {
        $user = $this->getUser();

        if (in_array('ROLE_PATIENT', $user->getRoles())) {
            $role = 'patient';
        } elseif (in_array('ROLE_MEDECIN', $user->getRoles())) {
            $role = 'medecin';
        } else {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que patient ou médecin.');
        }

        // Met à jour automatiquement les RDV confirmés passés à "réalisé"
        $rendezVousRepository->updatePastConfirmedToRealise();

        $etatId = $request->query->get('etat');
        $etatId = $etatId !== null && $etatId !== '' ? (int)$etatId : null;

        // Récupération des RDV selon le rôle
        if ($role === 'patient') {
            $rendezVous = $rendezVousRepository->findByPatientAndEtat($user, $etatId);
        } else {
            $rendezVous = $rendezVousRepository->findByMedecinAndEtat($user, $etatId);
        }

        return $this->render('mes_rendez_vous/index.html.twig', [
            'rendezVous' => $rendezVous,
            'etats' => $etatRepository->findAll(),
            'etatSelectionne' => $etatId,
            'role' => $role,
        ]);

    }
}
