<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\RecurringEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecurringEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecurringEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecurringEvent[]    findAll()
 * @method RecurringEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecurringEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecurringEvent::class);
    }
}
