<?php

namespace App\Controller;

use App\Entity\Assistant;
use App\Entity\Indication;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\RendezVous;
use App\Form\IndicationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndicationController extends AbstractController
{
    /**
     * Page de prescription d'un rendez-vous (médecin uniquement, en écriture).
     *
     * Le médecin saisit une indication (un médicament + sa posologie).
     * Après chaque enregistrement, on redirige vers la MÊME page afin de :
     *  - vider le formulaire (pattern POST-Redirect-GET, évite les double-submit),
     *  - et permettre la saisie d'une nouvelle indication pour un autre médicament,
     *  - tout en affichant la liste des indications déjà saisies sur ce RDV.
     */
    #[Route('/medecin/rendez-vous/{id}/prescrire', name: 'app_indication_new', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function new(
        Request $request,
        RendezVous $rendezVous,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        // Sécurité : seul le médecin propriétaire du rendez-vous peut prescrire.
        // Un assistant ou un patient ne doit pas pouvoir accéder à cette page.
        $isOwner = $user instanceof Medecin
            && $rendezVous->getMedecin()
            && $user->getId() === $rendezVous->getMedecin()->getId();

        if (!$isOwner) {
            throw $this->createAccessDeniedException('Seul le médecin du rendez-vous peut prescrire.');
        }

        // Nouvelle ligne d'indication, déjà rattachée au RDV courant
        $indication = new Indication();
        $indication->setRendezVous($rendezVous);

        $form = $this->createForm(IndicationType::class, $indication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($indication);
            $entityManager->flush();

            $this->addFlash('success', 'Indication ajoutée à la prescription.');

            // Redirection sur la même page : le médecin peut enchaîner une nouvelle indication.
            return $this->redirectToRoute('app_indication_new', ['id' => $rendezVous->getId()]);
        }

        return $this->render('indication/new.html.twig', [
            'rendezVous' => $rendezVous,
            // Indications déjà saisies pour ce RDV (collection inverse OneToMany)
            'indications' => $rendezVous->getLesIndications(),
            'form' => $form,
        ]);
    }

    /**
     * Modification d'une indication existante.
     *
     * Réservé au médecin titulaire du RDV. Ni le patient ni l'assistant
     * ne peuvent toucher au contenu d'une prescription.
     */
    #[Route('/medecin/indication/{id}/edit', name: 'app_indication_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        Request $request,
        Indication $indication,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $medecin = $indication->getRendezVous()->getMedecin();

        // Vérification : utilisateur connecté avec le rôle médecin ET médecin titulaire du RDV
        if (!$this->isGranted('ROLE_MEDECIN') || !$user instanceof Medecin || $user->getId() !== $medecin->getId()) {
            throw $this->createAccessDeniedException('Seul le médecin du rendez-vous peut modifier une indication.');
        }

        $form = $this->createForm(IndicationType::class, $indication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pas de persist() : l'entité est déjà managée par Doctrine
            $entityManager->flush();

            $this->addFlash('success', 'Indication mise à jour.');

            // Retour à la vue récapitulative de la prescription du RDV concerné
            return $this->redirectToRoute('app_indication_show', [
                'id' => $indication->getRendezVous()->getId(),
            ]);
        }

        return $this->render('indication/edit.html.twig', [
            'indication' => $indication,
            'rendezVous' => $indication->getRendezVous(),
            'form'       => $form,
        ]);
    }

    /**
     * Suppression d'une indication. POST + jeton CSRF obligatoires.
     * Réservé au médecin titulaire du RDV.
     */
    #[Route('/medecin/indication/{id}', name: 'app_indication_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        Request $request,
        Indication $indication,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $medecin = $indication->getRendezVous()->getMedecin();

        // Vérification : utilisateur connecté avec le rôle médecin ET médecin titulaire du RDV
        if (!$this->isGranted('ROLE_MEDECIN') || !$user instanceof Medecin || $user->getId() !== $medecin->getId()) {
            throw $this->createAccessDeniedException('Seul le médecin du rendez-vous peut supprimer une indication.');
        }

        // Protection CSRF : le token est généré côté vue avec le même identifiant
        if (!$this->isCsrfTokenValid('delete_indication' . $indication->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        // On garde l'id du RDV avant suppression pour pouvoir y revenir
        $rendezVousId = $indication->getRendezVous()->getId();

        $entityManager->remove($indication);
        $entityManager->flush();

        $this->addFlash('success', 'Indication supprimée.');

        return $this->redirectToRoute('app_indication_show', ['id' => $rendezVousId]);
    }

    /**
     * Affichage en LECTURE SEULE de la prescription d'un rendez-vous.
     *
     * Accessible aux 3 rôles :
     *  - le patient titulaire du RDV (pour consulter son traitement),
     *  - le médecin titulaire (pour relire ce qu'il a prescrit),
     *  - l'assistant rattaché à ce médecin (pour préparer la consultation / la facturation).
     */
    #[Route('/rendez-vous/{id}/prescription', name: 'app_indication_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(RendezVous $rendezVous): Response
    {
        $user = $this->getUser();
        $medecin = $rendezVous->getMedecin();
        $patient = $rendezVous->getPatient();

        // Sécurité : on autorise les 3 rôles, mais chacun uniquement sur SES propres RDV
        $isMedecin   = $user instanceof Medecin   && $medecin && $user->getId() === $medecin->getId();
        $isPatient   = $user instanceof Patient   && $patient && $user->getId() === $patient->getId();
        $isAssistant = $user instanceof Assistant && $user->getMedecin()
                       && $medecin && $user->getMedecin()->getId() === $medecin->getId();

        if (!$isMedecin && !$isPatient && !$isAssistant) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à consulter cette prescription.');
        }

        return $this->render('indication/show.html.twig', [
            'rendezVous'  => $rendezVous,
            'indications' => $rendezVous->getLesIndications(),
        ]);
    }
}
