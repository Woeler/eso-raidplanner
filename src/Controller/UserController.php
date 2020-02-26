<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Entity\DiscordChannel;
use App\Entity\DiscordGuild;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Form\User;
use App\Repository\DiscordChannelRepository;
use App\Repository\DiscordGuildRepository;
use App\Repository\GuildMembershipRepository;
use App\Security\Voter\GuildVoter;
use App\Service\DiscordBotService;
use App\Service\DiscordOauthService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user", name="user_")
 */
class UserController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DiscordGuildRepository
     */
    private $discordGuildRepository;

    public function __construct(EntityManagerInterface $entityManager, DiscordGuildRepository $discordGuildRepository)
    {
        $this->entityManager = $entityManager;
        $this->discordGuildRepository = $discordGuildRepository;
    }

    /**
     * @Route("/update", name="update")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(User::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', 'Your settings were updated');
        }

        return $this->render(
            'user/form.html.twig',
            [
                'form' => $form->createView(),
                'user' => $this->getUser(),
            ]
        );
    }

    /**
     * @Route("/guilds", name="guilds")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @return Response
     */
    public function viewDiscordGuilds(Request $request): Response
    {
        $guilds = $this->getUser()->getDiscordGuilds();

        // Needed for deleted "ghost" servers
        foreach ($guilds as $key => $guild) {
            if (!$guild->isMember($this->getUser())) {
                unset($guilds[$key]);
            }
        }

        return $this->render('user/discord_guilds/list.html.twig', ['guilds' => $guilds]);
    }

    /**
     * @Route("/guilds/refresh", name="guilds_refresh")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param DiscordOauthService $discordOauthService
     * @param GuildMembershipRepository $guildMembershipRepository
     * @return Response
     */
    public function refreshDiscordGuilds(Request $request, DiscordOauthService $discordOauthService, GuildMembershipRepository $guildMembershipRepository): Response
    {
        $guilds = $discordOauthService->getGuilds();
        $user = $this->getUser();
        $existingGuilds = new ArrayCollection();

        foreach ($guilds as $guild) {
            $newGuild = $this->entityManager->getRepository(DiscordGuild::class)
                ->findOneBy(['id' => $guild->id]);
            if (null === $newGuild) {
                $newGuild = new DiscordGuild();
            }
            $newGuild
                ->setName($guild->name)
                ->setDiscordId($guild->id)
                ->setIcon($guild->icon);
            if ($guild->owner) {
                $newGuild->setOwner($user)
                    ->makeAdmin($user);
            } else {
                $newGuild->addMember($user);
            }

            $this->entityManager->persist($newGuild);
            $existingGuilds->add($newGuild);
        }
        $this->entityManager->flush();

        foreach ($guildMembershipRepository->whereNotIn($user, $existingGuilds) as $membership) {
            $this->entityManager->remove($membership);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Guilds updated.');

        return $this->redirectToRoute('user_guilds');
    }

    /**
     * @Route("/guilds/{guildId}/botCheck", name="guild_bot_check")
     *
     * @param string $guildId
     * @param Request $request
     * @param DiscordBotService $discordBotService
     * @param DiscordChannelRepository $discordChannelRepository
     * @return Response
     */
    public function discordGuildBotIsActive(
        string $guildId,
        Request $request,
        DiscordBotService $discordBotService,
        DiscordChannelRepository $discordChannelRepository
    ): Response {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId, 'owner' => $this->getUser()->getId()]);

        if (null === $guild) {
            return $this->redirectToRoute('user_guilds');
        }

        try {
            $channels = $discordBotService->getChannels($guild->getId());
            $guild->setActive(true)
                ->setBotActive(true);
            $this->entityManager->persist($guild);

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

            $this->addFlash('success', 'Bot connected and guild activated!');

            return $this->redirectToRoute('guild_view', ['guildId' => $guild->getId()]);
        } catch (UnexpectedDiscordApiResponseException $e) {
            $this->addFlash('danger', 'The bot could not properly fetch data from your server. Does it have the correct rights?');
        }

        return $this->render('user/discord_guilds/bot_check.html.twig', ['guild' => $guild, 'clientId' => $this->getParameter('discord.bot.clientid')]);
    }

    /**
     * @Route("/guilds/{guildId}/nickname", name="guild_update_nickname")
     *
     * @param string $guildId
     * @param DiscordBotService $discordBotService
     * @param GuildMembershipRepository $guildMembershipRepository
     * @return Response
     */
    public function updateNickname(
        string $guildId,
        DiscordBotService $discordBotService,
        GuildMembershipRepository $guildMembershipRepository
    ): Response {
        $guild = $this->discordGuildRepository->find($guildId);
        $this->denyAccessUnlessGranted(GuildVoter::UPDATE_NICKNAME, $guild);
        try {
            $userdata = $discordBotService->getMember($guildId, $this->getUser()->getDiscordId());
            $membership = $guildMembershipRepository->findOneBy(['user' => $this->getUser(), 'guild' => $guild]);

            if (null !== $membership) {
                $membership->setNickname($userdata['nick'] ?? null);
                $this->entityManager->persist($membership);
                $this->entityManager->flush();
                $this->addFlash('success', 'Your nickname in the guild ' . $guild->getName() . ' was updated to ' . $membership->getNickname());
            }
        } catch (UnexpectedDiscordApiResponseException $e) {
            $this->addFlash('danger', 'Could not get nickname information from Discord');
        }

        return $this->redirectToRoute('user_guilds');
    }

    /**
     * @Route("/guilds/{guildId}/calendarvisibility", name="guild_update_guild_calendar_visibility")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param string $guildId
     * @param GuildMembershipRepository $guildMembershipRepository
     * @return Response
     */
    public function updateGuildCalendarVisibility(Request $request, string $guildId, GuildMembershipRepository $guildMembershipRepository): Response
    {
        $guild = $this->discordGuildRepository->find($guildId);
        $membership = $guildMembershipRepository->findOneBy(['user' => $this->getUser(), 'guild' => $guild]);

        if (null !== $membership) {
            $membership->setShowOnCalendar((bool)$request->get('show'));
            $this->entityManager->persist($membership);
            $this->entityManager->flush();

            return Response::create('Ok', Response::HTTP_OK);
        }

        return Response::create('Forbidden', Response::HTTP_FORBIDDEN);
    }
}
