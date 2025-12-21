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
use App\Entity\Medecin;
use App\Repository\IndisponibiliteRepository;


#[Route('/rendez/vous/new')]
final class RendezVousController extends AbstractController
{
    /*#[Route(name: 'app_rendez_vous_index', methods: ['GET'])]
    public function index(RendezVousRepository $rendezVousRepository): Response
    {
        return $this->render('rendez_vous/index.html.twig', [
            'rendez_vouses' => $rendezVousRepository->findAll(),
        ]);
    }*/

    #[Route(name: 'app_rendez_vous_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    IndisponibiliteRepository $indispoRepo,
    RendezVousRepository $rendezRepo
): Response {
    $user = $this->getUser();

    if (!$user instanceof Patient) {
        $this->addFlash('danger', 'Vous devez être connecté en tant que patient.');
        return $this->redirectToRoute('app_login');
    }

    $rendezVous = new RendezVous();
    $rendezVous->setPatient($user);

    $etat = $entityManager->getRepository(Etat::class)->find(1);
    $rendezVous->setEtat($etat);

    /* =========================
       PHASE 2 : clic sur créneau
       ========================= */
    if ($request->request->has('creneau')) {

        if (!$this->isCsrfTokenValid(
            'choix_creneau',
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException();
        }

        $creneau = $request->request->get('creneau');
        [$h, $m] = explode(':', $creneau);

        $date = new \DateTime($request->request->get('date'));
        $medecinId = $request->request->get('medecin');

        $medecin = $entityManager->getRepository(Medecin::class)->find($medecinId);
        $rendezVous->setMedecin($medecin);

        $debut = (clone $date)->setTime((int)$h, (int)$m);
        $rendezVous->setDebut($debut);
        $rendezVous->setFin((clone $debut)->modify('+1 hour'));

        $entityManager->persist($rendezVous);
        $entityManager->flush();

        $this->addFlash('success', 'Votre demande de rendez-vous a bien été envoyée.');

        return $this->redirectToRoute('app_mes_rendez_vous');
    }

    /* =========================
       PHASE 1 : date + médecin
       ========================= */
    $form = $this->createForm(RendezVousType::class, $rendezVous);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $medecin = $rendezVous->getMedecin();
        $date = $rendezVous->getDebut();

        $creneauxPossibles = [
            '09:00','09:30','10:00','10:30',
            '11:00','11:30','13:00','13:30',
            '14:00','14:30','15:00','15:30',
            '16:00','16:30',
        ];

        $creneauxDispo = [];

        foreach ($creneauxPossibles as $c) {
            [$h, $m] = explode(':', $c);
            $start = (clone $date)->setTime($h, $m);
            $end = (clone $start)->modify('+1 hour');

            if (
                empty($rendezRepo->findOverlapping($medecin, $start, $end)) &&
                empty($indispoRepo->findOverlapping($medecin, $start, $end))
            ) {
                $creneauxDispo[] = $c;
            }
        }

        return $this->render('rendez_vous/choix_creneau.html.twig', [
            'rendezVous' => $rendezVous,
            'creneaux' => $creneauxDispo,
        ]);
    }

    return $this->render('rendez_vous/new.html.twig', [
        'form' => $form,
    ]);
}

    /*

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
    }*/
}
