<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Patient;
final class AccueilController extends AbstractController
{
    #[Route('/test_patient', name: 'test_patient')]
    public function testPatient(EntityManagerInterface $em): Response
    {
        // Création d'un patient pour test
        $patient = new Patient();
        $patient->setEmail("p1@test.com");
        $patient->setPassword("azerty"); // pour test uniquement, plus tard il faudra hasher
        $patient->setRoles(["ROLE_PATIENT"]);

        $em->persist($patient);
        $em->flush();

        return new Response("Patient créé avec l'id : ".$patient->getId());
    }
    #[Route('/accueil', name: 'app_accueil')]
    public function index(): Response
    {
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
        ]);
    }

}
