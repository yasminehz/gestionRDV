<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Form\AssistantType;
use App\Repository\AssistantRepository;
use App\Repository\RendezVousRepository;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assistant')]
final class AssistantController extends AbstractController
{
    #[Route(name: 'app_assistant_index', methods: ['GET'])]
    public function index(AssistantRepository $assistantRepository): Response
    {
        return $this->render('assistant/index.html.twig', [
            'assistants' => $assistantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_assistant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $assistant = new Assistant();
        $form = $this->createForm(AssistantType::class, $assistant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($assistant);
            $entityManager->flush();

            return $this->redirectToRoute('app_assistant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assistant/new.html.twig', [
            'assistant' => $assistant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_assistant_show', methods: ['GET'])]
    public function show(Assistant $assistant): Response
    {
        return $this->render('assistant/show.html.twig', [
            'assistant' => $assistant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_assistant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Assistant $assistant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AssistantType::class, $assistant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_assistant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assistant/edit.html.twig', [
            'assistant' => $assistant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/rendezvous', name: 'app_assistant_rendezvous', methods: ['GET'])]
    public function rendezvous(Assistant $assistant, RendezVousRepository $rendezVousRepository, EtatRepository $etatRepository, Request $request): Response
    {
        $user = $this->getUser();

        // Allow only the assistant owner or admins to view the appointments
        if (!$this->isGranted('ROLE_ADMIN')) {
            if (!$user instanceof Assistant || $user->getId() !== $assistant->getId()) {
                throw $this->createAccessDeniedException('Accès non autorisé.');
            }
        }

        $etatId = $request->query->get('etat');
        $etatId = $etatId !== null && $etatId !== '' ? (int)$etatId : null;

        $jourFilter = $request->query->get('jour');
        $aujourdhui = $jourFilter === 'aujourdhui';

        $dateString = $request->query->get('date');
        $dateSpecifique = null;
        if ($dateString) {
            try {
                $dateSpecifique = new \DateTime($dateString);
            } catch (\Exception $e) {
                $dateSpecifique = null;
            }
        }

        $medecin = $assistant->getMedecin();

        // Met à jour automatiquement les RDV confirmés passés à "réalisé"
        $updated = $rendezVousRepository->updatePastConfirmedToRealise();
        if ($updated > 0) {
            $this->addFlash('success', sprintf('%d rendez-vous mis à jour en "réalisé".', $updated));
        }

        $rendezvous = [];
        if ($medecin) {
            $rendezvous = $rendezVousRepository->findByMedecinAndEtat($medecin, $etatId, $aujourdhui, $dateSpecifique);
        }

        return $this->render('mes_rendez_vous/index.html.twig', [
            'rendezVous' => $rendezvous,
            'etats' => $etatRepository->findAll(),
            'etatSelectionne' => $etatId,
            'jourFilter' => $jourFilter,
            'dateSelectionnee' => $dateString,
            'role' => 'assistant',
        ]);
    }

    #[Route('/{id}', name: 'app_assistant_delete', methods: ['POST'])]
    public function delete(Request $request, Assistant $assistant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$assistant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($assistant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_assistant_index', [], Response::HTTP_SEE_OTHER);
    }
}
