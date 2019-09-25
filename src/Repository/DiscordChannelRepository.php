<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\DiscordChannel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DiscordChannel|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiscordChannel|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiscordChannel[]    findAll()
 * @method DiscordChannel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscordChannelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DiscordChannel::class);
    }
}