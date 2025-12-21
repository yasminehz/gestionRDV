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
use App\Repository\EtatRepository;


#[Route('/rendez/vous')]
final class RendezVousController extends AbstractController
{
    #[Route(name: 'app_rendez_vous_index', methods: ['GET'])]
    public function index(RendezVousRepository $rendezVousRepository): Response
    {
        // Met à jour automatiquement les RDV confirmés passés à "réalisé"
        $rendezVousRepository->updatePastConfirmedToRealise();

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

            return $this->redirectToRoute('app_mes_rendez_vous', [], Response::HTTP_SEE_OTHER);
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

    #[Route('/{id}/etat', name: 'app_rendez_vous_change_etat', methods: ['POST'])]
    public function changeEtat(Request $request, RendezVous $rendezVou, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // permission: admin OR medecin owner OR assistant of the medecin
        $medecin = $rendezVou->getMedecin();

        $allowed = false;
        if ($this->isGranted('ROLE_ADMIN')) {
            $allowed = true;
        } elseif ($user instanceof \App\Entity\Medecin && $user->getId() === $medecin->getId()) {
            $allowed = true;
        } elseif ($user instanceof \App\Entity\Assistant && $user->getMedecin() && $user->getMedecin()->getId() === $medecin->getId()) {
            $allowed = true;
        }

        if (!$allowed) {
            throw $this->createAccessDeniedException('Accès non autorisé');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('change_etat'.$rendezVou->getId(), $token)) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_mes_rendez_vous');
        }

        $etatId = $request->request->get('etat');
        if ($etatId === null) {
            $this->addFlash('danger', 'État non spécifié.');
            return $this->redirectToRoute('app_mes_rendez_vous');
        }

        $etat = $etatRepository->find((int)$etatId);
        if (!$etat) {
            $this->addFlash('danger', 'État invalide.');
            return $this->redirectToRoute('app_mes_rendez_vous');
        }

        $rendezVou->setEtat($etat);
        $entityManager->flush();

        $this->addFlash('success', 'État mis à jour.');
        return $this->redirectToRoute('app_mes_rendez_vous');
    }
}
