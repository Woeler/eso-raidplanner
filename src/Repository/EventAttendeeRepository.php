<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\EventAttendee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EventAttendee|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventAttendee|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventAttendee[]    findAll()
 * @method EventAttendee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventAttendeeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EventAttendee::class);
    }
}
