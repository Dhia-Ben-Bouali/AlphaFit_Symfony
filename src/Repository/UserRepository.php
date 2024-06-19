<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
* @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
    /**
     * @param string|null $query
     * @return User[] Returns an array of User objects
     */
    public function searchUsers(?string $query)
    {
        $qb = $this->createQueryBuilder('u');

        if ($query) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('u.nom', ':query'),
                $qb->expr()->like('u.prenom', ':query'),
                $qb->expr()->like('u.adresse', ':query'),
                $qb->expr()->like('u.email', ':query')
            // Add more fields as needed
            ))
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb->getQuery()->getResult();
    }



// UserRepository.php

    public function countUsersByRole()
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('u.roles as role, COUNT(u.id) as userCount')
            ->andWhere('u.roles LIKE :nutritionistRole OR u.roles LIKE :coachRole')
            ->setParameter('nutritionistRole', '%"ROLE_NUTRITIONIST"%')
            ->setParameter('coachRole', '%"ROLE_COACH"%')
            ->groupBy('u.roles');

        $result = $queryBuilder->getQuery()->getResult();

        dump($result); // Add this line to see the result in the Symfony profiler

        return $result;
    }
    public function getUsersByRole($role)
    {
        $qb = $this->createQueryBuilder('u');

        // Si le rôle est 'user', incluez également les utilisateurs avec le rôle 'user'
        if ($role === 'user') {
            $qb->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        } else {
            $qb->andWhere('u.roles = :role')
                ->setParameter('role', $role);
        }

        return $qb->getQuery()->getResult();
    }
    public function getUsersByRoleAndSearch($role, $searchQuery)
    {
        $qb = $this->createQueryBuilder('u');

        // If a role is specified, filter by role
        if ($role) {
            // If the role is 'user', include users with the 'user' role as well
            if ($role === 'ROLE_USER') {
                $qb->andWhere('u.roles LIKE :role')
                    ->setParameter('role', '%' . $role . '%');
            } else {
                $qb->andWhere('u.roles LIKE :role')
                    ->setParameter('role', '%' . $role . '%');
            }
        }

        // If a search query is specified, filter by search
        if ($searchQuery) {
            $qb->andWhere('u.nom LIKE :searchQuery OR u.prenom LIKE :searchQuery OR u.adresse LIKE :searchQuery OR u.email LIKE :searchQuery')
                ->setParameter('searchQuery', '%' . $searchQuery . '%');
        }

        return $qb->getQuery()->getResult();
    }
//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
