<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Commande>
 *
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

//    /**
//     * @return Commande[] Returns an array of Commande objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Commande
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findByFirstName($firstName)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.firstName LIKE :firstName')
            ->setParameter('firstName', '%' . $firstName . '%')
            ->getQuery()
            ->getResult();
    }

    public function findTotalOfOrdersByDate()
    {
        // Create a QueryBuilder instance
        $qb = $this->createQueryBuilder('c');

        // Define the query
        $qb->select('SUBSTRING(c.date, 1, 10) as date', 'SUM(c.totale) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC');

        // Return the results as an array
        return $qb->getQuery()->getArrayResult();
    }
}
