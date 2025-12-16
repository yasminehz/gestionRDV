<?php

namespace App\Repository;

use App\Entity\Indisponibilite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Indisponibilite>
 */
class IndisponibiliteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Indisponibilite::class);
    }

    /**
     * Find indisponibilites for a medecin overlapping a given interval
     */
    public function findOverlapping($medecin, \DateTimeInterface $start, \DateTimeInterface $end)
    {
        $qb = $this->createQueryBuilder('i')
            ->andWhere('i.medecin = :m')
            ->andWhere('i.debut < :end')
            ->andWhere('i.fin > :start')
            ->setParameter('m', $medecin)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $qb->getQuery()->getResult();
    }
}
