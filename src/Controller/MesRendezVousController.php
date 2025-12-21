<?php

namespace App\Controller;

use App\Entity\Patient;
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
        /** @var Patient $patient */
        $patient = $this->getUser();

        if (!$patient instanceof Patient) {
            throw $this->createAccessDeniedException();
        }

        // Met à jour automatiquement les RDV confirmés passés à "réalisé"
        $rendezVousRepository->updatePastConfirmedToRealise();

        $etatId = $request->query->get('etat');
        $etatId = $etatId !== null && $etatId !== '' ? (int)$etatId : null;

        $rendezVous = $rendezVousRepository
            ->findByPatientAndEtat($patient, $etatId);


        return $this->render('mes_rendez_vous/index.html.twig', [
            'rendezVous' => $rendezVous,
            'etats' => $etatRepository->findAll(),
            'etatSelectionne' => $etatId,
        ]);
    }
}
