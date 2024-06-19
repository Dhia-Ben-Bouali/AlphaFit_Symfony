<?php

namespace App\Repository;

use App\Entity\Suivi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;


/**
 * @extends ServiceEntityRepository<Suivi>
 *
 * @method Suivi|null find($id, $lockMode = null, $lockVersion = null)
 * @method Suivi|null findOneBy(array $criteria, array $orderBy = null)
 * @method Suivi[]    findAll()
 * @method Suivi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuiviRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Suivi::class);
    }
   
// ...

// ...

public function getNombreCoursParTypeCoachPourUtilisateur(int $userId)
{
    $moisCourant = (new \DateTime())->format('m');
    $anneeCourante = (new \DateTime())->format('Y');

    return $this->createQueryBuilder('s')
        ->select('COUNT(s.id)')
        ->where('s.plan_coach = :coach')
        ->andWhere('s.user = :user')
        ->andWhere('MONTH(s.start) = :mois')
        ->andWhere('YEAR(s.start) = :annee')
        ->setParameter('coach', '1')
        ->setParameter('user', $userId)
        ->setParameter('mois', $moisCourant)
        ->setParameter('annee', $anneeCourante)
        ->getQuery()
        ->getSingleScalarResult();
}

public function getNombreCoursParTypeNutritionnistePourUtilisateur(int $userId)
{
    $moisCourant = (new \DateTime())->format('m');
    $anneeCourante = (new \DateTime())->format('Y');

    return $this->createQueryBuilder('s')
        ->select('COUNT(s.id)')
        ->where('s.plan_nutritionniste = :nutritionniste')
        ->andWhere('s.user = :user')
        ->andWhere('MONTH(s.start) = :mois')
        ->andWhere('YEAR(s.start) = :annee')
        ->setParameter('nutritionniste', '1')
        ->setParameter('user', $userId)
        ->setParameter('mois', $moisCourant)
        ->setParameter('annee', $anneeCourante)
        ->getQuery()
        ->getSingleScalarResult();
}




public function getNombreCoursSportParJour(int $userId, \DateTime $premierJour, \DateTime $dernierJour): array
{
    return $this->createQueryBuilder('s')
        ->select('DAY(s.start) as jour, COUNT(s.id) as nombreCours')
        ->where('s.user = :userId')
        ->andWhere('s.plan_coach IS NOT NULL') // Pour les cours de sport uniquement
        ->andWhere('s.start BETWEEN :premierJour AND :dernierJour')
        ->setParameter('userId', $userId)
        ->setParameter('premierJour', $premierJour)
        ->setParameter('dernierJour', $dernierJour)
        ->groupBy('jour')
        ->orderBy('jour')
        ->getQuery()
        ->getResult();
}



}

