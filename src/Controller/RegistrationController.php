<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\Assistant;
use App\Model\RegistrationModel;
use App\Entity\User; // Garder User pour le type hinting et les propriétés communes
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use LogicException;
use App\Repository\MedecinRepository;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    #[Route('/register', name: 'app_register')]
public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MedecinRepository $repo): Response
{
    // On utilise un DTO/simple objet interne au formulaire, pas User
    $model = new RegistrationModel();
    $medecins = $repo->getLesMedecins();
    $form = $this->createForm(RegistrationFormType::class, $model, [
        'medecins_choix' => $medecins,
    ]);

    $form->handleRequest($request);
 



    if ($form->isSubmitted() && $form->isValid()) {
        
        $userType = $form->get('userType')->getData();
        $plainPassword = $form->get('plainPassword')->getData();

        // Ici on instancie la sous-classe réelle
        switch ($userType) {
            case 'patient':
                $entityToPersist = new Patient();
                break;
            case 'medecin':
                $entityToPersist = new Medecin();
                break;
            case 'assistant':
                $entityToPersist = new Assistant();
                break;
            default:
                throw new LogicException("Type d'utilisateur non supporté.");
        }

        // Hydratation
        $entityToPersist->setNom($form->get('nom')->getData());
        $entityToPersist->setPrenom($form->get('prenom')->getData());
        $entityToPersist->setEmail($form->get('email')->getData());

        // Uniquement l’assistant a un médecin
        if ($entityToPersist instanceof Assistant) {
            $entityToPersist->setMedecin($form->get('medecin')->getData());
        }

        // Mot de passe
        $entityToPersist->setPassword(
            $userPasswordHasher->hashPassword($entityToPersist, $plainPassword)
        );

        $entityManager->persist($entityToPersist);
        $entityManager->flush();

        return $this->redirectToRoute('app_accueil');
    }

    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form->createView(),
    ]);
}

}