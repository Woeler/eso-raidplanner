<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Controller\Checks\GuildMemberCheckController;
use App\Entity\DiscordChannel;
use App\Entity\Event;
use App\Entity\EventAttendee;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Repository\DiscordChannelRepository;
use App\Repository\DiscordGuildRepository;
use App\Repository\EventAttendeeRepository;
use App\Repository\EventRepository;
use App\Service\DiscordBotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/guild", name="guild_")
 */
class GuildController extends AbstractController implements GuildMemberCheckController
{
    /**
     * @var DiscordGuildRepository
     */
    private $discordGuildRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EventAttendeeRepository
     */
    private $eventAttendeeRepository;

    public function __construct(
        DiscordGuildRepository $discordGuildRepository,
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository,
        EventAttendeeRepository $eventAttendeeRepository
    ) {
        $this->discordGuildRepository = $discordGuildRepository;
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->eventAttendeeRepository = $eventAttendeeRepository;
    }

    /**
     * @Route("/{guildId}", name="view")
     *
     * @param string $guildId
     * @return Response
     */
    public function view(string $guildId): Response
    {
        return $this->render(
            'guild/view.html.twig',
            [
                'guild' => $this->discordGuildRepository->findOneBy(['id' => $guildId]),
            ]
        );
    }

    /**
     * @Route("/{guildId}/settings", name="settings")
     *
     * @param string $guildId
     * @return Response
     */
    public function settings(string $guildId): Response
    {
        return $this->render(
            'guild/settings.html.twig',
            [
                'guild' => $this->discordGuildRepository->findOneBy(['id' => $guildId]),
            ]
        );
    }

    /**
     * @Route("/{guildId}/settings/discord/sync", name="settings_discord_sync")
     *
     * @param string $guildId
     * @param DiscordBotService $discordBotService
     * @param DiscordChannelRepository $discordChannelRepository
     * @return Response
     */
    public function syncDiscordChannels(
        string $guildId,
        DiscordBotService $discordBotService,
        DiscordChannelRepository $discordChannelRepository
    ): Response {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        try {
            $channels = $discordBotService->getChannels($guild->getId());

            foreach ($channels as $channel) {
                if (DiscordChannel::CHANNEL_TYPE_TEXT !== $channel['type']) {
                    continue;
                }

                $discordChannel = $discordChannelRepository->find($channel['id']);
                if (null === $discordChannel) {
                    $discordChannel = new DiscordChannel();
                }

                $discordChannel->setId($channel['id'])
                    ->setGuild($guild)
                    ->setName('#'.$channel['name'])
                    ->setType(DiscordChannel::CHANNEL_TYPE_TEXT)
                    ->setError(DiscordChannel::ERROR_NONE);

                if (null !== $channel['parent_id']) {
                    $discordChannel->setName($channels[$channel['parent_id']]['name'].' > '.$discordChannel->getName());
                }

                $this->entityManager->persist($discordChannel);
            }
            $this->entityManager->flush();

            $this->addFlash('success', 'Channels were synced.');
        } catch (UnexpectedDiscordApiResponseException $e) {
            $this->addFlash('danger', 'The bot could not properly fetch data from your server. Does it have the correct rights?');
        }

        return $this->redirectToRoute('guild_settings', ['guildId' => $guildId]);
    }

    /**
     * @Route("/{guildId}/event/{eventId}/view", name="event_view")
     *
     * @param string $guildId
     * @param int $eventId
     * @param Request $request
     * @return Response
     */
    public function viewEvent(string $guildId, int $eventId, Request $request): Response
    {
        $event = $this->eventRepository->find($eventId);
        $attendee = $this->eventAttendeeRepository->findOneBy(['user' => $this->getUser()->getId(), 'event' => $eventId]);
        $attending = true;
        if (null === $attendee) {
            $attendee = new EventAttendee();
            $attending = false;
        }
        $form = $this->createForm(\App\Form\EventAttendee::class, $attendee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attendee->setUser($this->getUser())
                ->setEvent($event);
            $this->entityManager->persist($attendee);
            $this->entityManager->flush();
            $this->addFlash('success', 'Event attendance updated.');

            return $this->redirectToRoute('guild_event_view', ['guildId' => $guildId, 'eventId' => $eventId]);
        }

        return $this->render(
            'event/view.html.twig',
            [
                'event' => $event,
                'guild' => $this->discordGuildRepository->find($guildId),
                'form' => $form->createView(),
                'attending' => $attending,
            ]
        );
    }

    /**
     * @Route("/{guildId}/event/create", name="event_create")
     *
     * @param string $guildId
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function createEvent(string $guildId, Request $request): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $event = new Event();
        $event->setStart(new \DateTime('now'));
        $form = $this->createForm(\App\Form\Event::class, $event, ['timezone' => $this->getUser()->getTimezone(), 'clock' => $this->getUser()->getClock()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setGuild($guild);
            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $this->addFlash('success', 'Event '.$event->getName().' created.');

            return $this->redirectToRoute('guild_view', ['guildId' => $guildId]);
        }

        return $this->render(
            'event/form.html.twig',
            [
                'form' => $form->createView(),
                'guild' => $guild,
            ]
        );
    }

    /**
     * @Route("/{guildId}/event/{eventId}/update", name="event_update")
     *
     * @param string $guildId
     * @param int $eventId
     * @param Request $request
     * @return Response
     */
    public function updateEvent(string $guildId, int $eventId, Request $request): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $event = $this->eventRepository->find($eventId);
        $form = $this->createForm(\App\Form\Event::class, $event, ['timezone' => $this->getUser()->getTimezone(), 'clock' => $this->getUser()->getClock()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $this->addFlash('success', 'Event '.$event->getName().' updated.');

            return $this->redirectToRoute('guild_view', ['guildId' => $guildId]);
        }

        return $this->render(
            'event/form.html.twig',
            [
                'form' => $form->createView(),
                'guild' => $guild,
            ]
        );
    }

    /**
     * @Route("/{guildId}/event/{eventId}/delete", name="event_delete")
     *
     * @param string $guildId
     * @param int $eventId
     * @param Request $request
     * @return Response
     */
    public function deleteEvent(string $guildId, int $eventId, Request $request): Response
    {
        $event = $this->eventRepository->find($eventId);

        $this->entityManager->remove($event);
        $this->entityManager->flush();
        $this->addFlash('success', 'Event was deleted.');

        return $this->redirectToRoute('guild_view', ['guildId' => $guildId]);
    }

    /**
     * @Route("/{guildId}/event/{eventId}/unattend", name="event_unattend")
     *
     * @param string $guildId
     * @param int $eventId
     * @param Request $request
     * @return Response
     */
    public function eventUnattend(string $guildId, int $eventId, Request $request): Response
    {
        $attendee = $this->eventAttendeeRepository->findOneBy(['user' => $this->getUser()->getId(), 'event' => $eventId]);

        if (null !== $attendee) {
            $this->entityManager->remove($attendee);
            $this->entityManager->flush();

            $this->addFlash('success', 'You are no longer attending this event.');
        }

        return $this->redirectToRoute('guild_event_view', ['guildId' => $guildId, 'eventId' => $eventId]);
    }
}
