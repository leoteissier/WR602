<?php

namespace App\Repository;

use App\Entity\Subcription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subcription>
 *
 * @method Subcription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subcription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subcription[]    findAll()
 * @method Subcription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubcriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subcription::class);
    }

//    /**
//     * @return Subcription[] Returns an array of Subcription objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Subcription
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
