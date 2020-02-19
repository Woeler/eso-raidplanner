<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller\Api;

use App\Repository\DiscordGuildRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/api/ical", name="api_ical_")
 */
class IcalController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var DiscordGuildRepository
     */
    private $guildRepository;

    public function __construct(
        UserRepository $userRepository,
        EventRepository $eventRepository,
        DiscordGuildRepository $guildRepository,
        UrlGeneratorInterface $router
    ) {
        $this->userRepository = $userRepository;
        $this->eventRepository = $eventRepository;
        $this->router = $router;
        $this->guildRepository = $guildRepository;
    }

    /**
     * @Route("/user/calendar/{icalId}", name="user_calendar")
     *
     * @param Request $request
     * @param string $icalId
     * @return Response
     * @throws \Exception
     */
    public function userCalendar(Request $request, string $icalId): Response
    {
        $calendar = new Calendar('esoraidplanner.com');
        $user = $this->userRepository->findOneBy(['icalId' => $icalId]);
        $onlyAttending = null !== $request->get('onlyAttending');

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $events = $this->eventRepository->findEventsForUserBetween(
            $user,
            new \DateTime('-50 days'),
            new \DateTime('+50 days')
        );

        foreach ($events as $event) {
            if ($onlyAttending && !$event->isAttending($user)) {
                continue;
            }
            $calendar->addEvent(
                (new Event())
                    ->setDtStart($event->getStart())
                    ->setUrl($this->router->generate(
                        'guild_event_view',
                        [
                            'guildId' => $event->getGuild()->getDiscordId(),
                            'eventId' => $event->getId(),
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ))
                    ->setDescription($event->getName().' ('.$event->getGuild()->getName().')')
            );
        }

        return $this->render(
            'ical/ical.html.twig',
            [
                'calendar' => $calendar,
            ]
        );
    }

    /**
     * @Route("/guild/calendar/{icalId}", name="guild_calendar")
     *
     * @param Request $request
     * @param string $icalId
     * @return Response
     * @throws \Exception
     */
    public function guildCalendar(Request $request, string $icalId): Response
    {
        $calendar = new Calendar('esoraidplanner.com');
        $guild = $this->guildRepository->findOneBy(['icalId' => $icalId]);

        if (null === $guild) {
            throw new NotFoundHttpException();
        }

        $events = $this->eventRepository->findEventsForGuildBetween(
            $guild,
            new \DateTime('-50 days'),
            new \DateTime('+50 days')
        );

        foreach ($events as $event) {
            $calendar->addEvent(
                (new Event())
                    ->setDtStart($event->getStart())
                    ->setUrl($this->router->generate(
                        'guild_event_view',
                        [
                            'guildId' => $event->getGuild()->getDiscordId(),
                            'eventId' => $event->getId(),
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ))
                    ->setDescription($event->getName())
            );
        }

        return $this->render(
            'ical/ical.html.twig',
            [
                'calendar' => $calendar,
            ]
        );
    }
}
