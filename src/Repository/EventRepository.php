<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\Event;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param DateTime $dateTime
     * @return Event[]
     */
    public function findFutureEvents(DateTime $dateTime)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.start > :now')
            ->setParameter('now', $dateTime->format('Y-m-d H:i:s'))
            ->orderBy('e.start', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
