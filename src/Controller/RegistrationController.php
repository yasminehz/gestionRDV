<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\Assistant;
use App\Entity\User; // Garder User pour le type hinting et les propriétés communes
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use LogicException;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // On crée un objet temporaire de type User pour initialiser le formulaire.
        // C'est l'objet final créé qui sera persisté.
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $userType = $form->get('userType')->getData();
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            
            // 1. Déterminer et créer l'entité fille correcte
            // On utilise les données du formulaire, mais on crée une nouvelle instance
            // de l'entité spécifique.
            
            switch ($userType) {
                case 'patient':
                    $entityToPersist = new Patient();
                    break;
                case 'medecin':
                    $entityToPersist = new Medecin();
                    break;
                case 'assistant':
                    $entityToPersist = new Assistant();
                    
                    // TODO: Gérer l'affectation du médecin pour l'assistant ici
                    // Ceci nécessite d'ajouter un champ #id_medecin au formulaire pour l'assistant
                    // ou de gérer cette affectation plus tard.
                    // Si #id_medecin ne peut être nul, l'inscription échouera ici.
                    
                    break;
                default:
                    throw new LogicException('Type d\'utilisateur non supporté.');
            }
       
            $entityToPersist->setNom($user->getNom()); 
            $entityToPersist->setPrenom($user->getPrenom());
            $entityToPersist->setEmail($user->getEmail()); // Assurez-vous d'utiliser la bonne méthode (getEmail/getMail)
          

            
            
            // 3. Encoder le mot de passe
            $entityToPersist->setPassword($userPasswordHasher->hashPassword($entityToPersist, $plainPassword));

            // 4. Persister l'entité fille (qui est aussi un User)
            $entityManager->persist($entityToPersist);
            $entityManager->flush();

            // ...

            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}