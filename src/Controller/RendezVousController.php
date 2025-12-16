<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Patient;
use App\Entity\Etat;
use App\Repository\IndisponibiliteRepository;


#[Route('/rendez/vous')]
final class RendezVousController extends AbstractController
{
    #[Route(name: 'app_rendez_vous_index', methods: ['GET'])]
    public function index(RendezVousRepository $rendezVousRepository): Response
    {
        return $this->render('rendez_vous/index.html.twig', [
            'rendez_vouses' => $rendezVousRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_rendez_vous_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, IndisponibiliteRepository $indispoRepo, RendezVousRepository $rendezRepo): Response
    {
        $user = $this->getUser();

        // if not logged in, redirect to login
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // if logged in but not a Patient, show a flash message and redirect to accueil
        if (!$user instanceof Patient) {
            $this->addFlash('danger', 'vous devez être connecté en tant que patient pour prendre un rdv');

            return $this->redirectToRoute('app_accueil');
        }

        $rendezVou = new RendezVous();
        // assign current patient automatically
        $rendezVou->setPatient($user);

        // set default Etat (id = 1) if it exists
        $defaultEtat = $entityManager->getRepository(Etat::class)->find(1);
        if ($defaultEtat) {
            $rendezVou->setEtat($defaultEtat);
        }
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // combine selected date and chosen time slot (creneau) into debut
            $creneau = null;
            if ($form->has('creneau')) {
                $creneau = $form->get('creneau')->getData();
            }

            $date = $rendezVou->getDebut(); // DateTime with 00:00 time since DateType used
            if ($date instanceof \DateTimeInterface && $creneau) {
                [$h, $m] = explode(':', $creneau);
                $dt = (clone $date)->setTime((int)$h, (int)$m);
                $rendezVou->setDebut($dt);
                $rendezVou->setFin((clone $dt)->modify('+1 hour'));
                // server-side availability check
                $start = $rendezVou->getDebut();
                $end = $rendezVou->getFin();
                $rvs = $rendezRepo->findOverlapping($rendezVou->getMedecin(), $start, $end);
                $indis = $indispoRepo->findOverlapping($rendezVou->getMedecin(), $start, $end);
                if (!empty($rvs) || !empty($indis)) {
                    $this->addFlash('danger', 'Le créneau n\'est plus disponible, choisissez un autre créneau.');
                    return $this->redirectToRoute('app_rendez_vous_new');
                }
            } else {
                // fallback: set fin relative to debut if present
                $debut = $rendezVou->getDebut();
                if ($debut instanceof \DateTimeInterface) {
                    $rendezVou->setFin((clone $debut)->modify('+1 hour'));
                }
            }

            $entityManager->persist($rendezVou);
            $entityManager->flush();

            return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rendez_vous/new.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rendez_vous_show', methods: ['GET'])]
    public function show(RendezVous $rendezVou): Response
    {
        return $this->render('rendez_vous/show.html.twig', [
            'rendez_vou' => $rendezVou,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rendez_vous_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // combine selected date and chosen time slot (creneau) into debut when editing
            $creneau = null;
            if ($form->has('creneau')) {
                $creneau = $form->get('creneau')->getData();
            }

            $date = $rendezVou->getDebut();
            if ($date instanceof \DateTimeInterface && $creneau) {
                [$h, $m] = explode(':', $creneau);
                $dt = (clone $date)->setTime((int)$h, (int)$m);
                $rendezVou->setDebut($dt);
                $rendezVou->setFin((clone $dt)->modify('+1 hour'));
            } else {
                $debut = $rendezVou->getDebut();
                if ($debut instanceof \DateTimeInterface) {
                    $rendezVou->setFin((clone $debut)->modify('+1 hour'));
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rendez_vous/edit.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rendez_vous_delete', methods: ['POST'])]
    public function delete(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rendezVou->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rendezVou);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
    }
}
