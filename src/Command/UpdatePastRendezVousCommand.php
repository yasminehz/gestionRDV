<?php

namespace App\Command;

use App\Repository\EtatRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-past-rendez-vous',
    description: 'Change l\'état des rendez-vous confirmés passés à "réalisé"',
)]
class UpdatePastRendezVousCommand extends Command
{
    public function __construct(
        private RendezVousRepository $rendezVousRepository,
        private EtatRepository $etatRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupère l'état par ID (préféré) avec fallback libellé
        $etatConfirme = $this->etatRepository->find(2)
            ?? $this->etatRepository->findOneBy(['libelle' => 'confirmé']);
        if (!$etatConfirme) {
            $io->error('État "confirmé" introuvable');
            return Command::FAILURE;
        }

        // Récupère l'état par ID (préféré) avec fallback libellé
        $etatRealise = $this->etatRepository->find(5)
            ?? $this->etatRepository->findOneBy(['libelle' => 'réalisé'])
            ?? $this->etatRepository->findOneBy(['libelle' => 'realisé']);
        if (!$etatRealise) {
            $io->error('État "réalisé" introuvable');
            return Command::FAILURE;
        }

        // Trouve tous les RDV confirmés dont la fin est passée
        $now = new \DateTime();
        $rendezVousPasses = $this->rendezVousRepository->createQueryBuilder('r')
            ->where('r.etat = :etatConfirme')
            ->andWhere('r.fin < :now')
            ->setParameter('etatConfirme', $etatConfirme)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        $count = count($rendezVousPasses);
        
        if ($count === 0) {
            $io->success('Aucun rendez-vous confirmé passé à mettre à jour.');
            return Command::SUCCESS;
        }

        // Met à jour l'état de chaque RDV
        foreach ($rendezVousPasses as $rdv) {
            $rdv->setEtat($etatRealise);
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d rendez-vous confirmé(s) passé(s) à l\'état "réalisé".', $count));

        return Command::SUCCESS;
    }
}
