<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Repository;

use App\Entity\DiscordGuild;
use App\Entity\Event;
use App\Entity\RecurringEvent;
use App\Entity\User;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    /**
     * @param RecurringEvent $recurringEvent
     * @return Event[]
     */
    public function findFutureEventsByRecurring(RecurringEvent $recurringEvent)
    {
        $dt = new DateTime();

        return $this->createQueryBuilder('e')
            ->andWhere('e.start > :now')
            ->andWhere('e.recurringParent = :parent')
            ->setParameter('now', $dt->format('Y-m-d H:i:s'))
            ->setParameter('parent', $recurringEvent)
            ->orderBy('e.start', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param DiscordGuild $guild
     * @return Event[]
     */
    public function findFutureEventsForGuild(DiscordGuild $guild): array
    {
        $dateTime = new DateTime('now', new DateTimeZone('UTC'));

        return $this->createQueryBuilder('e')
            ->andWhere('e.start > :now')
            ->andWhere('e.guild = :guild')
            ->setParameter('now', $dateTime->format('Y-m-d H:i:s'))
            ->setParameter('guild', $guild)
            ->orderBy('e.start', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @return Event[]
     */
    public function findFutureEventsForUser(User $user): array
    {
        $dateTime = new DateTime('now', new DateTimeZone('UTC'));
        $guilds = new ArrayCollection();
        foreach ($user->getGuildMemberships() as $membership) {
            $guilds->add($membership->getGuild());
        }

        return $this->createQueryBuilder('e')
            ->andWhere('e.start > :now')
            ->andWhere('e.guild IN (:guilds)')
            ->setParameter('now', $dateTime->format('Y-m-d H:i:s'))
            ->setParameter('guilds', $guilds)
            ->orderBy('e.start', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param DateTime $start
     * @param DateTime $end
     * @return Event[]
     */
    public function findEventsForUserBetween(User $user, DateTime $start, DateTime $end): array
    {
        $guilds = new ArrayCollection();
        foreach ($user->getGuildMemberships() as $membership) {
            $guilds->add($membership->getGuild());
        }

        return $this->createQueryBuilder('e')
            ->andWhere('e.start >= :start')
            ->andWhere('e.start < :end')
            ->andWhere('e.guild IN (:guilds)')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('guilds', $guilds)
            ->orderBy('e.start', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
