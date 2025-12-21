<?php

namespace App\Repository;

use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RendezVous>
 */
class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    /**
     * Find rendezvous for a medecin overlapping a given interval
     *
     * @return RendezVous[]
     */
    public function findOverlapping($medecin, \DateTimeInterface $start, \DateTimeInterface $end)
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.medecin = :m')
            ->andWhere('r.debut < :end')
            ->andWhere('r.fin > :start')
            ->setParameter('m', $medecin)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return RendezVous[] Returns an array of RendezVous objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
 /**
 * @return RendezVous[]
 */
    public function findByPatientAndEtat(Patient $patient, ?int $etatId): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.patient = :patient')
            ->setParameter('patient', $patient)
            ->orderBy('r.debut', 'ASC');

        if ($etatId) {
            $qb->andWhere('r.etat = :etat')
               ->setParameter('etat', $etatId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return RendezVous[]
     */
    public function findByMedecinAndEtat(Medecin $medecin, ?int $etatId): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.medecin = :medecin')
            ->setParameter('medecin', $medecin)
            ->orderBy('r.debut', 'ASC');

        if ($etatId !== null) {
            $qb->andWhere('r.etat = :etat')
               ->setParameter('etat', $etatId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Met à jour les rendez-vous confirmés passés à l'état "réalisé"
     */
    public function updatePastConfirmedToRealise(): int
    {
        $em = $this->getEntityManager();
        
        // Récupère les états par ID (préféré), fallback libellé si nécessaire
        $etatRepo = $em->getRepository(\App\Entity\Etat::class);
        $etatConfirme = $etatRepo->find(2) ?? $etatRepo->findOneBy(['libelle' => 'confirmé']);
        $etatRealise = $etatRepo->find(5)
            ?? $etatRepo->findOneBy(['libelle' => 'réalisé'])
            ?? $etatRepo->findOneBy(['libelle' => 'realisé']);

        if (!$etatConfirme || !$etatRealise) {
            return 0;
        }

        // Sélectionne les RDV à mettre à jour puis met à jour entité par entité
        // Utilise le fuseau Europe/Paris pour éviter les décalages
        $tz = new \DateTimeZone('Europe/Paris');
        $now = new \DateTimeImmutable('now', $tz);
        $toUpdate = $this->createQueryBuilder('r')
            ->where('r.etat = :etatConfirme')
            ->andWhere('r.fin < :now')
            ->setParameter('etatConfirme', $etatConfirme)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        foreach ($toUpdate as $rdv) {
            $rdv->setEtat($etatRealise);
        }

        if (!empty($toUpdate)) {
            $em->flush();
        }

        return count($toUpdate);
    }
}
