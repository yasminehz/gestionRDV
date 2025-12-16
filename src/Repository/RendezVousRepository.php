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
            ->setParameter('medecin', $medecin);

        if ($etatId !== null) {
            $qb->andWhere('r.etat = :etat')
            ->setParameter('etat', $etatId);
        }

        return $qb->getQuery()->getResult();
    }

}
