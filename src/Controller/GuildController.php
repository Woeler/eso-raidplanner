<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Entity\DiscordChannel;
use App\Entity\Event;
use App\Entity\EventAttendee;
use App\Entity\GuildMembership;
use App\Entity\Reminder;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Form\DiscordGuildType;
use App\Form\ReminderType;
use App\Repository\DiscordChannelRepository;
use App\Repository\DiscordGuildRepository;
use App\Repository\EventAttendeeRepository;
use App\Repository\EventRepository;
use App\Repository\GuildMembershipRepository;
use App\Repository\ReminderRepository;
use App\Repository\UserRepository;
use App\Security\Voter\EventVoter;
use App\Security\Voter\GuildVoter;
use App\Security\Voter\ReminderVoter;
use App\Service\DiscordBotService;
use App\Service\GuildLoggerService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/guild", name="guild_")
 */
class GuildController extends AbstractController
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

    /**
     * @var GuildLoggerService
     */
    private $guildLoggerService;

    public function __construct(
        DiscordGuildRepository $discordGuildRepository,
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository,
        EventAttendeeRepository $eventAttendeeRepository,
        GuildLoggerService $guildLoggerService
    ) {
        $this->discordGuildRepository = $discordGuildRepository;
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->eventAttendeeRepository = $eventAttendeeRepository;
        $this->guildLoggerService = $guildLoggerService;
    }

    /**
     * @Route("/{guildId}", name="view")
     *
     * @param string $guildId
     * @return Response
     */
    public function view(string $guildId): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::VIEW, $guild);

        $upcomingEvents = $this->eventRepository->findFutureEventsForGuild($guild);

        return $this->render(
            'guild/view.html.twig',
            [
                'guild' => $guild,
                'events' => $upcomingEvents,
            ]
        );
    }

    /**
     * @Route("/{guildId}/settings", name="settings")
     *
     * @param string $guildId
     * @param Request $request
     * @return Response
     */
    public function settings(string $guildId, Request $request): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::VIEW_SETTINGS, $guild);

        $form = $this->createForm(DiscordGuildType::class, $guild, ['guild' => $guild]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($guild);
            $this->entityManager->flush();
            $this->addFlash('success', 'Guild settings updated.');

            return $this->redirectToRoute('guild_view', ['guildId' => $guildId]);
        }

        return $this->render(
            'guild/settings.html.twig',
            [
                'guild' => $guild,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{guildId}/members", name="members")
     *
     * @param string $guildId
     * @return Response
     */
    public function members(string $guildId): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::VIEW_MEMBERS, $guild);

        return $this->render(
            'guild/members.html.twig',
            [
                'guild' => $guild,
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
        $this->denyAccessUnlessGranted(GuildVoter::SYNC_DISCORD_CHANNELS, $guild);

        try {
            $channels = $discordBotService->getChannels($guild->getId());
            $existingChannels = new ArrayCollection();

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
                $existingChannels->add($discordChannel);
            }
            $this->entityManager->flush();

            foreach ($discordChannelRepository->whereNotIn($guild, $existingChannels) as $channel) {
                $this->entityManager->remove($channel);
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
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);

        $attendee = $this->eventAttendeeRepository->findOneBy(['user' => $this->getUser()->getId(), 'event' => $eventId]);
        $attending = true;
        if (null === $attendee) {
            $attendee = new EventAttendee();
            $attending = false;
        }
        $form = $this->createForm(\App\Form\EventAttendee::class, $attendee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(EventVoter::ATTEND, $event);

            $attendee->setUser($this->getUser())
                ->setEvent($event);
            $this->entityManager->persist($attendee);
            $this->entityManager->flush();
            if (!$attending) {
                $this->guildLoggerService->eventAttending($event->getGuild(), $event, $attendee);
            }
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
        $this->denyAccessUnlessGranted(GuildVoter::CREATE_EVENT, $guild);

        $event = new Event();
        $event->setStart(new \DateTime('now'));
        $form = $this->createForm(\App\Form\Event::class, $event, ['timezone' => $this->getUser()->getTimezone(), 'clock' => $this->getUser()->getClock()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setGuild($guild);
            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $this->guildLoggerService->eventCreated($guild, $event);
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
        $this->denyAccessUnlessGranted(EventVoter::UPDATE, $event);

        $form = $this->createForm(\App\Form\Event::class, $event, ['timezone' => $this->getUser()->getTimezone(), 'clock' => $this->getUser()->getClock()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $this->guildLoggerService->eventCreated($guild, $event);
            $this->addFlash('success', 'Event '.$event->getName().' updated.');

            return $this->redirectToRoute('guild_view', ['guildId' => $guildId]);
        }

        return $this->render(
            'event/form.html.twig',
            [
                'form' => $form->createView(),
                'guild' => $guild,
                'update' => true,
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
        $this->denyAccessUnlessGranted(EventVoter::DELETE, $event);

        $this->guildLoggerService->eventDeleted($event->getGuild(), $event);
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
        $event = $this->eventRepository->find($eventId);
        $this->denyAccessUnlessGranted(EventVoter::UNATTEND, $event);

        $guid = $this->discordGuildRepository->find($guildId);

        if (null !== $attendee) {
            $this->guildLoggerService->eventUnattending($guid, $event, $attendee);
            $this->entityManager->remove($attendee);
            $this->entityManager->flush();

            $this->addFlash('success', 'You are no longer attending this event.');
        }

        return $this->redirectToRoute('guild_event_view', ['guildId' => $guildId, 'eventId' => $eventId]);
    }

    /**
     * @Route("/{guildId}/reminder/create", name="reminder_create")
     *
     * @param int $guildId
     * @param Request $request
     * @return Response
     */
    public function createReminder(int $guildId, Request $request): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::CREATE_REMINDER, $guild);
        $reminder = new Reminder();
        $form = $this->createForm(ReminderType::class, $reminder, ['guild' => $guild]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reminder->setGuild($guild);
            $this->entityManager->persist($reminder);
            $this->entityManager->flush();
            $this->addFlash('success', 'Reminder '.$reminder->getName().' created.');

            return $this->redirectToRoute('guild_reminder_list', ['guildId' => $guildId]);
        }

        return $this->render(
            'reminder/form.html.twig',
            [
                'form' => $form->createView(),
                'guild' => $guild,
            ]
        );
    }

    /**
     * @Route("/{guildId}/reminder/{reminderId}/update", name="reminder_update")
     *
     * @param int $guildId
     * @param int $reminderId
     * @param Request $request
     * @param ReminderRepository $reminderRepository
     * @return Response
     */
    public function updateReminder(int $guildId, int $reminderId, Request $request, ReminderRepository $reminderRepository): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $reminder = $reminderRepository->find($reminderId);
        $this->denyAccessUnlessGranted(ReminderVoter::UPDATE, $reminder);

        $form = $this->createForm(ReminderType::class, $reminder, ['guild' => $guild]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($reminder);
            $this->entityManager->flush();
            $this->addFlash('success', 'Reminder '.$reminder->getName().' updated.');

            return $this->redirectToRoute('guild_reminder_list', ['guildId' => $guildId]);
        }

        return $this->render(
            'reminder/form.html.twig',
            [
                'form' => $form->createView(),
                'guild' => $guild,
            ]
        );
    }

    /**
     * @Route("/{guildId}/reminder/{reminderId}/delete", name="reminder_delete")
     *
     * @param string $guildId
     * @param int $reminderId
     * @param Request $request
     * @param ReminderRepository $reminderRepository
     * @return Response
     */
    public function deleteReminder(string $guildId, int $reminderId, Request $request, ReminderRepository $reminderRepository): Response
    {
        $reminder = $reminderRepository->find($reminderId);
        $this->denyAccessUnlessGranted(ReminderVoter::DELETE, $reminder);

        $this->entityManager->remove($reminder);
        $this->entityManager->flush();
        $this->addFlash('success', 'Reminder was deleted.');

        return $this->redirectToRoute('guild_reminder_list', ['guildId' => $guildId]);
    }

    /**
     * @Route("/{guildId}/reminders", name="reminder_list")
     *
     * @param string $guildId
     * @return Response
     */
    public function reminders(string $guildId): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::CREATE_REMINDER, $guild);

        return $this->render(
            'reminder/list.html.twig',
            [
                'guild' => $guild,
            ]
        );
    }

    /**
     * @Route("/{guildId}/member/{userId}/promote", name="member_promote")
     *
     * @param string $guildId
     * @param string $userId
     * @param UserRepository $userRepository
     * @param GuildMembershipRepository $guildMembershipRepository
     * @return Response
     */
    public function promoteToAdmin(string $guildId, string $userId, UserRepository $userRepository, GuildMembershipRepository $guildMembershipRepository): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::PROMOTE, $guild);
        $user = $userRepository->findOneBy(['discordId' => $userId]);

        if (null === $user || $guild->isAdmin($user)) {
            return $this->redirectToRoute('guild_members', ['guildId' => $guildId]);
        }

        $membership = $guildMembershipRepository->findOneBy(['user' => $user, 'guild' => $guild]);
        $membership->setRole(GuildMembership::ROLE_ADMIN);
        $this->entityManager->persist($membership);
        $this->entityManager->flush();
        $this->addFlash('success', $user->getUsername().' was promoted to admin.');

        return $this->redirectToRoute('guild_members', ['guildId' => $guildId]);
    }

    /**
     * @Route("/{guildId}/member/{userId}/demote", name="member_demote")
     *
     * @param string $guildId
     * @param string $userId
     * @param UserRepository $userRepository
     * @param GuildMembershipRepository $guildMembershipRepository
     * @return Response
     */
    public function demoteToMember(string $guildId, string $userId, UserRepository $userRepository, GuildMembershipRepository $guildMembershipRepository): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::DEMOTE, $guild);

        $user = $userRepository->findOneBy(['discordId' => $userId]);

        if (null === $user || !$guild->isAdmin($user)) {
            return $this->redirectToRoute('guild_members', ['guildId' => $guildId]);
        }

        $membership = $guildMembershipRepository->findOneBy(['user' => $user, 'guild' => $guild]);
        $membership->setRole(GuildMembership::ROLE_MEMBER);
        $this->entityManager->persist($membership);
        $this->entityManager->flush();
        $this->addFlash('success', $user->getUsername().' was demoted to member.');

        return $this->redirectToRoute('guild_members', ['guildId' => $guildId]);
    }
}
