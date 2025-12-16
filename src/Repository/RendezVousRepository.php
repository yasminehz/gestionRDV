<?php

namespace App\Repository;

use App\Entity\Patient;
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
}
