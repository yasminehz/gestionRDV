<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Form\AssistantType;
use App\Repository\AssistantRepository;
use App\Repository\RendezVousRepository;
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
    public function rendezvous(Assistant $assistant, RendezVousRepository $rendezVousRepository, Request $request): Response
    {
        $etat = $request->query->get('etat');
        $medecin = $assistant->getMedecin();

        $rendezvous = [];
        if ($medecin) {
            $rendezvous = $rendezVousRepository->findByMedecinAndEtat($medecin, $etat !== null ? (int) $etat : null);
        }

        return $this->render('assistant/rendezvous.html.twig', [
            'assistant' => $assistant,
            'rendezvous' => $rendezvous,
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
