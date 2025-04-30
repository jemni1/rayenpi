<?php

namespace App\Repository;

use App\Entity\Route;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Route>
 */
class RouteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Route::class);
    }

     
    /**
     * Find routes by starting location
     * 
     * @param string $term Search term
     * @return Route[] Returns an array of Route objects
     */
    public function findByStartLocation(string $term): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('LOWER(r.startLocation) LIKE LOWER(:term)')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Find routes by destination
     * 
     * @param string $term Search term
     * @return Route[] Returns an array of Route objects
     */
    public function findByEndLocation(string $term): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('LOWER(r.endLocation) LIKE LOWER(:term)')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Route[] Returns an array of Route objects
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

    //    public function findOneBySomeField($value): ?Route
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
