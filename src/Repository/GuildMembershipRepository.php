<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\GuildMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GuildMembership|null find($id, $lockMode = null, $lockVersion = null)
 * @method GuildMembership|null findOneBy(array $criteria, array $orderBy = null)
 * @method GuildMembership[]    findAll()
 * @method GuildMembership[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GuildMembershipRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GuildMembership::class);
    }
}
