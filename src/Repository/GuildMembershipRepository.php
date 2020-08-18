<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\GuildMembership;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method GuildMembership|null find($id, $lockMode = null, $lockVersion = null)
 * @method GuildMembership|null findOneBy(array $criteria, array $orderBy = null)
 * @method GuildMembership[]    findAll()
 * @method GuildMembership[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GuildMembershipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuildMembership::class);
    }

    public function whereNotIn(User $user, Collection $guilds): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.guild NOT IN (:guilds)')
            ->andWhere('e.user = :user')
            ->setParameter('guilds', $guilds)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
