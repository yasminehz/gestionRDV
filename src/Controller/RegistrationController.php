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
            
            // 2. Transférer les données communes de l'objet temporaire vers l'entité fille
            // ATTENTION : Cette partie dépend de si vous avez un DTO ou de la façon dont 
            // RegistrationFormType est construit. Pour la simplicité, supposons que nous copions 
            // les champs communs (nom, mail, etc.) manuellement si le formulaire était lié à User.
            
            // Si vous utilisez le DTO/FormType comme ci-dessus, cette étape est simplifiée si vous utilisez:
            // $form = $this->createForm(RegistrationFormType::class); // sans $user

            // Pour que ce code fonctionne avec l'héritage Doctrine (Single Table Inheritance),
            // il est plus simple de définir les propriétés communes sur l'entité parente User 
            // et de créer directement l'entité fille.
            
            // Étant donné que le formulaire a rempli $user, on va copier ces valeurs.
            
            // Copie manuelle des champs de l'objet $user vers $entityToPersist :
            $entityToPersist->setNom($user->getNom()); 
            $entityToPersist->setPrenom($user->getPrenom());
            $entityToPersist->setEmail($user->getMail()); // Assurez-vous d'utiliser la bonne méthode (getEmail/getMail)
            $entityToPersist->setNumero($user->getNumero()); 
            // ... autres setters de User
            
            
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