<?php

namespace App\Controller;

use App\Entity\Indisponibilite;
use App\Form\IndisponibiliteType;
use App\Repository\IndisponibiliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Medecin;

#[Route('/indisponibilite')]
class IndisponibiliteController extends AbstractController
{
    #[Route('/', name: 'indisponibilite_index', methods: ['GET'])]
    public function index(IndisponibiliteRepository $repo): Response
    {
        $medecin = $this->getUser();
        if (!($medecin instanceof Medecin) && ! $this->isGranted('ROLE_MEDECIN')) {
            throw $this->createAccessDeniedException();
        }
        $items = $repo->findBy(['medecin' => $medecin], ['debut' => 'DESC']);

        return $this->render('indisponibilite/index.html.twig', [
            'indisponibilites' => $items,
        ]);
    }

    #[Route('/new', name: 'indisponibilite_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em, IndisponibiliteRepository $repo): Response
    {
        $medecin = $this->getUser();
        if (!($medecin instanceof Medecin) && ! $this->isGranted('ROLE_MEDECIN')) {
            throw $this->createAccessDeniedException();
        }
        $indispo = new Indisponibilite();
        $indispo->setMedecin($medecin);

        $form = $this->createForm(IndisponibiliteType::class, $indispo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // validate no overlap
            $overlaps = $repo->findOverlapping($medecin, $indispo->getDebut(), $indispo->getFin());
            if (count($overlaps) > 0) {
                $this->addFlash('danger', 'Ce créneau chevauche une indisponibilité existante.');
            } else {
                $em->persist($indispo);
                $em->flush();
                $this->addFlash('success', 'Indisponibilité ajoutée.');
                return $this->redirectToRoute('indisponibilite_index');
            }
        }

        $existing = $repo->findBy(['medecin' => $medecin], ['debut' => 'ASC']);

        return $this->render('indisponibilite/new.html.twig', [
            'indisponibilite' => $indispo,
            'form' => $form->createView(),
            'existing' => $existing,
        ]);
    }

    #[Route('/{id}/edit', name: 'indisponibilite_edit', methods: ['GET','POST'])]
    public function edit(Indisponibilite $indispo, Request $request, EntityManagerInterface $em, IndisponibiliteRepository $repo): Response
    {
        $medecin = $this->getUser();
        if (!($medecin instanceof Medecin) && ! $this->isGranted('ROLE_MEDECIN')) {
            throw $this->createAccessDeniedException();
        }
        if ($indispo->getMedecin()->getId() !== $medecin->getId()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(IndisponibiliteType::class, $indispo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $overlaps = $repo->findOverlapping($medecin, $indispo->getDebut(), $indispo->getFin());
            // allow self in overlaps check
            $overlaps = array_filter($overlaps, fn($o) => $o->getId() !== $indispo->getId());
            if (count($overlaps) > 0) {
                $this->addFlash('danger', 'Ce créneau chevauche une indisponibilité existante.');
            } else {
                $em->flush();
                $this->addFlash('success', 'Indisponibilité mise à jour.');
                return $this->redirectToRoute('indisponibilite_index');
            }
        }

        $existing = $repo->findBy(['medecin' => $medecin], ['debut' => 'ASC']);

        return $this->render('indisponibilite/edit.html.twig', [
            'indisponibilite' => $indispo,
            'form' => $form->createView(),
            'existing' => $existing,
        ]);
    }

    #[Route('/{id}', name: 'indisponibilite_delete', methods: ['POST'])]
    public function delete(Indisponibilite $indispo, Request $request, EntityManagerInterface $em): Response
    {
        $medecin = $this->getUser();
        if (!($medecin instanceof Medecin) && ! $this->isGranted('ROLE_MEDECIN')) {
            throw $this->createAccessDeniedException();
        }
        if ($indispo->getMedecin()->getId() !== $medecin->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$indispo->getId(), $request->request->get('_token'))) {
            $em->remove($indispo);
            $em->flush();
            $this->addFlash('success', 'Indisponibilité supprimée.');
        }

        return $this->redirectToRoute('indisponibilite_index');
    }
}
