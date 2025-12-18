<?php

namespace App\Repository;

use App\Entity\Activite;
use App\Entity\Instance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activite>
 */
class ActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activite::class);
    }

    public function findByStatut(string $statut)
    {
        return $this->createQueryBuilder('a')
            ->addSelect('i')
            ->join('a.instance', 'i')
            ->where('a.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()->getResult()
            ;
    }

    // src/Repository/ActiviteRepository.php

    public function countActivitiesByMonthForRegion(Instance $region): array
    {
        return $this->createQueryBuilder('a')
            ->select("SUBSTRING(a.dateDebut, 1, 7) as month, COUNT(a.id) as count")
            ->join('a.instance', 'i')
            // On cherche l'instance elle-même (la Région)
            // OU son parent (District -> Région)
            // OU le parent du parent (Groupe -> District -> Région)
            ->leftJoin('i.instanceParent', 'p')
            ->leftJoin('p.instanceParent', 'gp')
            ->where('i = :region OR p = :region OR gp = :region')
            ->setParameter('region', $region)
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Activite[] Returns an array of Activite objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Activite
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
