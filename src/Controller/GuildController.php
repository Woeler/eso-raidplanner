<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Entity\DiscordChannel;
use App\Entity\GuildMembership;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Form\DiscordGuildType;
use App\Repository\DiscordChannelRepository;
use App\Repository\DiscordGuildRepository;
use App\Repository\EventRepository;
use App\Repository\GuildMembershipRepository;
use App\Repository\UserRepository;
use App\Security\Voter\GuildVoter;
use App\Service\DiscordBotService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/guild", name="guild_")
 */
class GuildController extends AbstractController
{
    private DiscordGuildRepository $discordGuildRepository;
    private EntityManagerInterface $entityManager;
    private EventRepository $eventRepository;

    public function __construct(
        DiscordGuildRepository $discordGuildRepository,
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository
    ) {
        $this->discordGuildRepository = $discordGuildRepository;
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route("/{guildId}", name="view")
     * @IsGranted("ROLE_USER")
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
     * @IsGranted("ROLE_USER")
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
     * @IsGranted("ROLE_USER")
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
     * @Route("/{guildId}/past-events", name="past_events")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param string $guildId
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function pastEvents(Request $request, string $guildId, PaginatorInterface $paginator): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::VIEW_PAST_EVENTS, $guild);
        $events = $this->eventRepository->findPastEventsForGuild($guild);

        $pagination = $paginator->paginate(
            $events,
            $request->query->getInt('page', 1),
            25
        );

        return $this->render(
            'guild/past_events.html.twig',
            [
                'guild' => $guild,
                'paginator' => $pagination,
            ]
        );
    }

    /**
     * @Route("/{guildId}/settings/discord/sync", name="settings_discord_sync")
     * @IsGranted("ROLE_USER")
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
            $roles = $discordBotService->getServerRoles($guild->getId());
            $userRoles = $discordBotService->getBotUser($guild->getId())['roles'];
            $existingChannels = new ArrayCollection();

            foreach ($channels as $channel) {
                if (DiscordChannel::CHANNEL_TYPE_TEXT !== $channel['type']) {
                    continue;
                }
                if (!$discordBotService->canViewChannel($roles, $userRoles, $channel['permission_overwrites'])) {
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
     * @Route("/{guildId}/member/{userId}/promote", name="member_promote")
     * @IsGranted("ROLE_USER")
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
     * @IsGranted("ROLE_USER")
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

    /**
     * @Route("/{guildId}/member/{userId}/remove", name="member_remove")
     * @IsGranted("ROLE_USER")
     *
     * @param string $guildId
     * @param string $userId
     * @param UserRepository $userRepository
     * @param GuildMembershipRepository $guildMembershipRepository
     * @return Response
     */
    public function removeMembership(string $guildId, string $userId, UserRepository $userRepository, GuildMembershipRepository $guildMembershipRepository): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::REMOVE_MEMBER, $guild);

        if ($this->getUser()->getDiscordId() !== (int)$userId) {
            $user = $userRepository->findOneBy(['discordId' => $userId]);
            $membership = $guildMembershipRepository->findOneBy(['user' => $user, 'guild' => $guild]);
            $this->entityManager->remove($membership);
            $this->entityManager->flush();
            $this->addFlash('success', $user->getUsername() . ' was removed.');
        }

        return $this->redirectToRoute('guild_members', ['guildId' => $guildId]);
    }

    /**
     * @Route("/{guildId}/deactivate", name="deactivate")
     * @IsGranted("ROLE_USER")
     *
     * @param string $guildId
     * @param DiscordBotService $discordBotService
     * @return Response
     */
    public function deactivate(string $guildId, DiscordBotService $discordBotService): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::DEACTIVATE, $guild);

        try {
            $discordBotService->leaveServer($guild->getDiscordId());
        } catch (UnexpectedDiscordApiResponseException $e) {
            // Nothing, the bot is probably already gone from the server
        }

        $guild->setActive(false);
        $this->entityManager->persist($guild);
        foreach ($guild->getRecurringEvents() as $recurringEvent) {
            $this->entityManager->remove($recurringEvent);
        }
        $this->entityManager->flush();
        $this->addFlash('danger', 'Guild '.$guild->getName().' was deactivated.');

        return $this->redirectToRoute('home');
    }
}
