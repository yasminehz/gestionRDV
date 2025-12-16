<?php

namespace App\Controller;

use App\Entity\Medecin;
use App\Repository\RendezVousRepository;
use App\Repository\IndisponibiliteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MedecinAvailabilityController extends AbstractController
{
    #[Route('/medecin/{id}/available-slots', name: 'medecin_available_slots', methods: ['GET'])]
    public function slots(Medecin $medecin, Request $request, RendezVousRepository $rendezRepo, IndisponibiliteRepository $indispoRepo): JsonResponse
    {
        $date = $request->query->get('date'); // YYYY-MM-DD
        if (!$date) {
            return $this->json(['error' => 'date required'], 400);
        }

        $dayStart = new \DateTime($date . ' 00:00');
        $dayEnd = (clone $dayStart)->modify('+1 day');

        // define working hours (configurable later)
        $workStart = (clone $dayStart)->setTime(9, 0);
        $workEnd = (clone $dayStart)->setTime(17, 0);

        $slots = [];
        $cursor = clone $workStart;
        while ($cursor < $workEnd) {
            $slotStart = clone $cursor;
            $slotEnd = (clone $slotStart)->modify('+1 hour');
            // check overlap with rendezvous
            $rvs = $rendezRepo->createQueryBuilder('r')
                ->andWhere('r.medecin = :m')
                ->andWhere('r.debut < :end')
                ->andWhere('r.fin > :start')
                ->setParameters(['m' => $medecin, 'start' => $slotStart, 'end' => $slotEnd])
                ->getQuery()->getResult();

            $indis = $indispoRepo->findOverlapping($medecin, $slotStart, $slotEnd);

            $available = empty($rvs) && empty($indis);

            $slots[] = [
                'label' => $slotStart->format('H:i'),
                'value' => $slotStart->format('H:i'),
                'available' => $available,
            ];

            $cursor->modify('+30 minutes'); // stepping could be 30m but slots are 1h; step 30m to allow half-hour boundaries if needed
        }

        return $this->json($slots);
    }
}
