<?php

namespace App\Controller;

use App\Entity\Prescription;
use App\Entity\RendezVous;
use App\Form\PrescriptionType;
use App\Repository\PrescriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/prescription')]
final class PrescriptionController extends AbstractController
{
    #[Route(name: 'app_prescription_index', methods: ['GET'])]
    public function index(PrescriptionRepository $prescriptionRepository): Response
    {
        return $this->render('prescription/index.html.twig', [
            'prescriptions' => $prescriptionRepository->findAll(),
        ]);
    }

    #[Route('/new/{rendezVous}', name: 'app_prescription_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
    {
        $prescription = new Prescription();
        $prescription->setRendezVous($rendezVous);

        $form = $this->createForm(PrescriptionType::class, $prescription, [
            'include_rendez_vous' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Convertir les jours en heures (format DateTime)
            if ($prescription->getDuree()) {
                $days = (int) $prescription->getDuree()->format("H");
                $prescription->setDuree($this->convertDaysToDateTime($days));
            }
            $entityManager->persist($prescription);
            $entityManager->flush();

            return $this->redirectToRoute('app_prescription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prescription/new.html.twig', [
            'prescription' => $prescription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_prescription_show', methods: ['GET'])]
    public function show(Prescription $prescription): Response
    {
        return $this->render('prescription/show.html.twig', [
            'prescription' => $prescription,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_prescription_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Prescription $prescription, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PrescriptionType::class, $prescription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Convertir les jours en heures (format DateTime)
            if ($prescription->getDuree()) {
                $days = (int) $prescription->getDuree()->format("H");
                $prescription->setDuree($this->convertDaysToDateTime($days));
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_prescription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prescription/edit.html.twig', [
            'prescription' => $prescription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_prescription_delete', methods: ['POST'])]
    public function delete(Request $request, Prescription $prescription, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$prescription->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($prescription);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_prescription_index', [], Response::HTTP_SEE_OTHER);
    }

    private function convertDaysToDateTime(int $days): \DateTime
    {
        $heures = $days * 24;
        $dateTime = new \DateTime();
        $dateTime->setTime($heures % 24, 0, 0);
        return $dateTime;
    }
}
