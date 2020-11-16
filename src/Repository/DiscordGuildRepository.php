<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\DiscordGuild;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DiscordGuild|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiscordGuild|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiscordGuild[]    findAll()
 * @method DiscordGuild[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscordGuildRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscordGuild::class);
    }
}
