<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\Assistant;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Form\FormError;
final class AccueilController extends AbstractController
{
    #[Route('/test_patient', name: 'test_patient')]
    public function testPatient(EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
{
        // Création d'un patient pour test
        $patient = new Patient();
        $patient->setEmail('testP@test.com');
        $patient->setRoles(['ROLE_PATIENT']);
        $patient->setPassword($userPasswordHasher->hashPassword($patient, 'azerty'));
        try{
        $em->persist($patient);
        $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new Response('Erreur : cet email est déjà utilisé.');

        return new Response("Medecin créé avec l'id : ".$medecin->getId());
        }

        return new Response("Patient créé avec l'id : ".$patient->getId());
    }

    #[Route('/test_medecin', name: 'test_medecin')]
    public function testMedecin(EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
{
        // Création d'un médecin pour test
        $medecin = new Medecin();
        $medecin->setEmail('testM@test.com');
        $medecin->setRoles(['ROLE_MEDECIN']);
        $medecin->setPassword($userPasswordHasher->hashPassword($medecin, 'azerty'));
        try{
        $em->persist($medecin);
        $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new Response('Erreur : cet email est déjà utilisé.');

        return new Response("Medecin créé avec l'id : ".$medecin->getId());
        }
    }

    #[Route('/test_assistant', name: 'test_assistant')]
    public function testAssistant(EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
{
        // Création d'un médecin pour test
        $assistant = new Assistant();
        $assistant->setEmail('testA@test.com');
        $assistant->setRoles(['ROLE_MEDECIN']);
        $assistant->setPassword($userPasswordHasher->hashPassword($assistant, 'azerty'));
        try{
        $em->persist($assistant);
        $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new Response('Erreur : cet email est déjà utilisé.');
        }

        return new Response("Assistant créé avec l'id : ".$assistant->getId());
    }

    #[Route('/accueil', name: 'app_accueil')]
    public function index(): Response
    {
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
        ]);
    }

    #[Route('/', name:'homepage')]
    public function homepage(): Response
    {
        return $this->render('accueil/index.html.twig',[
            'controller_name' => 'AccueilController',
    ]);
    }
}