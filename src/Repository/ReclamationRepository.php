<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 *
 * @method Reclamation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reclamation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reclamation[]    findAll()
 * @method Reclamation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

//    /**
//     * @return Reclamation[] Returns an array of Reclamation objects
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

//    public function findOneBySomeField($value): ?Reclamation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findAllWithUser(): array
        {
            return $this->createQueryBuilder('r')
                ->leftJoin('r.recuser', 'u')
                ->addSelect('u')
                ->getQuery()
                ->getResult();
        }


    public function findByDateAndEtat($searchDate, $searchEtat)
        {
            $queryBuilder = $this->createQueryBuilder('r');
    
            if ($searchDate !== null & $searchEtat !== null) {
                $queryBuilder->andWhere('r.date = :date')
                             ->setParameter('date', $searchDate);
                $queryBuilder->andWhere('r.etat = :etat')
                             ->setParameter('etat', $searchEtat);
            }
            elseif ($searchEtat !== null & $searchDate == null) {
                $queryBuilder->andWhere('r.etat = :etat')
                             ->setParameter('etat', $searchEtat);
            }

            elseif ($searchDate !== null & $searchEtat == null) {
                $queryBuilder->andWhere('r.date = :date')
                             ->setParameter('date', $searchDate);
            }
            return $queryBuilder->getQuery()->getResult();
        }

        

}

