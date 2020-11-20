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
    private UserRepository $userRepository;
    private EventRepository $eventRepository;
    private UrlGeneratorInterface $router;
    private DiscordGuildRepository $guildRepository;

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

        $calendar
            ->setName('ESO Raidplanner')
            ->setDescription(
                'ESO Raidplanner personal calendar for '.$user->getUsername().'#'.$user->getDiscordDiscriminator()
            );

        $events = $this->eventRepository->findEventsForUserBetween(
            $user,
            new \DateTime('-50 days'),
            new \DateTime('+50 days')
        );

        foreach ($events as $event) {
            if ($onlyAttending && !$event->isAttending($user)) {
                continue;
            }
            $calEvent = (new Event())
                ->setSummary($event->getName().' ('.$event->getGuild()->getName().')')
                ->setDtStart($event->getStart())
                ->setUrl($this->router->generate(
                    'guild_event_view',
                    [
                        'guildId' => $event->getGuild()->getDiscordId(),
                        'eventId' => $event->getId(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ))
                ->setDescription($event->getName().' ('.$event->getGuild()->getName().')');
            if (null !== $event->getEnd()) {
                $calEvent->setDtEnd($event->getEnd());
            }
            $calendar->addComponent($calEvent);
        }

        return new Response(
            $calendar->render(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="cal.ics"',
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

        $calendar
            ->setName('ESO Raidplanner')
            ->setDescription('ESO Raidplanner guild calendar for '.$guild->getName());

        $events = $this->eventRepository->findEventsForGuildBetween(
            $guild,
            new \DateTime('-50 days'),
            new \DateTime('+50 days')
        );

        foreach ($events as $event) {
            $calEvent = (new Event())
                ->setSummary($event->getName())
                ->setDtStart($event->getStart())
                ->setUrl($this->router->generate(
                    'guild_event_view',
                    [
                        'guildId' => $event->getGuild()->getDiscordId(),
                        'eventId' => $event->getId(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ))
                ->setDescription($event->getName());
            if (null !== $event->getEnd()) {
                $calEvent->setDtEnd($event->getEnd());
            }
            $calendar->addComponent($calEvent);
        }

        return new Response(
            $calendar->render(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="cal.ics"',
            ]
        );
    }
}
