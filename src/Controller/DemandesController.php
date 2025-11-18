<?php

namespace App\Controller;

use App\Entity\Demandes;
use App\Form\DemandesType;
use App\Repository\DemandesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/demandes')]
final class DemandesController extends AbstractController
{
    #[Route(name: 'app_demandes_index', methods: ['GET'])]
    public function index(DemandesRepository $demandesRepository): Response
    {
        return $this->render('demandes/index.html.twig', [
            'demandes' => $demandesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_demandes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $demande = new Demandes();
        $form = $this->createForm(DemandesType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($demande);
            $entityManager->flush();

            return $this->redirectToRoute('app_demandes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demandes/new.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demandes_show', methods: ['GET'])]
    public function show(Demandes $demande): Response
    {
        return $this->render('demandes/show.html.twig', [
            'demande' => $demande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_demandes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demandes $demande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DemandesType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_demandes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demandes/edit.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demandes_delete', methods: ['POST'])]
    public function delete(Request $request, Demandes $demande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_demandes_index', [], Response::HTTP_SEE_OTHER);
    }
}
