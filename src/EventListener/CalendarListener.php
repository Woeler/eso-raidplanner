<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\EventListener;

use App\Entity\GuildMembership;
use App\Entity\User;
use App\Repository\EventRepository;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CalendarListener
{
    private EventRepository $eventRepository;
    private TokenStorageInterface $tokenStorage;
    private UrlGeneratorInterface $router;

    public function __construct(
        EventRepository $eventRepository,
        TokenStorageInterface $tokenStorage,
        UrlGeneratorInterface $router
    ) {
        $this->eventRepository = $eventRepository;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
    }

    public function load(CalendarEvent $calendar): void
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $events = $this->eventRepository->findCalendarEvents($user, $start, $end);

        foreach ($events as $event) {
            if ($event->getStart()->getTimestamp() >= $start->getTimestamp() && $event->getStart()->getTimestamp() < $end->getTimestamp()) {
                $eventTime = $event->getStart();
                $eventTime->setTimezone(new \DateTimeZone($user->getTimezone()));
                $eventEnd = null;
                if (null !== $event->getEnd()) {
                    $eventEnd = $event->getEnd();
                    $eventEnd->setTimezone(new \DateTimeZone($user->getTimezone()));
                }

                $calendarEvent = new Event(
                    $eventTime->format(24 === $user->getClock() ? 'H:i' : 'g:ia').': '.$event->getName(),
                    $eventTime,
                    $eventEnd
                );
                $calendarEvent->addOption('url', $this->router->generate(
                    'guild_event_view',
                    [
                        'guildId' => $event->getGuild()->getId(),
                        'eventId' => $event->getId(),
                    ]
                ));
                $calendarEvent->addOption('guild', $event->getGuild()->getName());
                $calendarEvent->addOption('attending', count($event->getAttendees()));
                $calendarEvent->addOption('start-time', $eventTime->format(24 === $user->getClock() ? 'H:i' : 'g:ia'));

                if (null !== $event->getEnd()) {
                    $calendarEvent->addOption('end-time', $eventEnd->format(24 === $user->getClock() ? 'H:i' : 'g:ia'));
                }

                $guild = $event->getGuild();
                $colour = $user->getActiveGuildMemberships()->filter(static function (GuildMembership $m) use ($guild) {
                    return $m->getGuild()->getId() === $guild->getId();
                });
                $colour = $colour->first()->getColour();
                $calendarEvent->addOption('backgroundColor', '#'.$colour);
                $calendarEvent->addOption('borderColor', '#'.$colour);

                $calendar->addEvent($calendarEvent);
            }
        }
    }
}
