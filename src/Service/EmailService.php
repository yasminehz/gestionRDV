<?php

namespace App\Service;

use App\Entity\RendezVous;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig
    ) {
    }

    public function sendRendezVousConfirmationEmail(RendezVous $rendezVous): void
    {
        $patient = $rendezVous->getPatient();
        $medecin = $rendezVous->getMedecin();

        if (!$patient || !$patient->getEmail()) {
            return;
        }

        $email = (new Email())
            ->from('noreply@gestionrdv.com')
            ->to($patient->getEmail())
            ->subject('Votre rendez-vous a été confirmé')
            ->html(
                $this->twig->render('emails/rendez_vous_confirmation.html.twig', [
                    'rendezVous' => $rendezVous,
                    'patient' => $patient,
                    'medecin' => $medecin,
                ])
            );

        $this->mailer->send($email);
    }
}
