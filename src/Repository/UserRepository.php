<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return array|User[]
     */
    public function findWhereTokenAlmostExpires(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.discordTokenExpirationDate <  :date')
            ->setParameter('date', new \DateTime('+8 day'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $ids
     * @return array|User[]
     */
    public function findWherePatronAndNotIn(array $ids): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.discordId NOT IN (:ids)')
            ->andWhere('u.patreonMembership > :none')
            ->setParameter('ids', $ids)
            ->setParameter('none', 0)
            ->getQuery()
            ->getResult();
    }
}
