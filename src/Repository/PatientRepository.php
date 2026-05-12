<?php

namespace App\Repository;

use App\Entity\Medecin;
use App\Entity\Medicament;
use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Patient>
 */
class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    /**
     * Retourne la liste DISTINCTE des patients à qui un médicament a été prescrit
     * dans le cadre d'un rendez-vous avec le médecin passé en paramètre.
     *
     * Chemin de la jointure : Patient ← RendezVous ← Indication → Medicament.
     * Le DISTINCT évite qu'un patient apparaisse plusieurs fois s'il a reçu
     * plusieurs prescriptions du même médicament.
     *
     * @return Patient[]
     */
    public function findByMedicamentAndMedecin(Medicament $medicament, Medecin $medecin): array
    {
        return $this->createQueryBuilder('p')
            ->distinct()
            // p (patient) -> r (rendezVous) -> i (indication) -> m (medicament)
            ->innerJoin('p.lesRendezVous', 'r')
            ->innerJoin('r.lesIndications', 'i')
            ->andWhere('i.medicament = :medicament')
            ->andWhere('r.medecin = :medecin')
            ->setParameter('medicament', $medicament)
            ->setParameter('medecin', $medecin)
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Patient[] Returns an array of Patient objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Patient
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
