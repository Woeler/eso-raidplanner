<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Entity\DiscordChannel;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Form\User;
use App\Repository\DiscordChannelRepository;
use App\Repository\DiscordGuildRepository;
use App\Service\DiscordBotService;
use Doctrine\ORM\EntityManagerInterface;
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
     *
     * @param Request $request
     * @return Response
     */
    public function viewDiscordGuilds(Request $request): Response
    {
        $guilds = $this->getUser()->getDiscordGuilds();

        return $this->render('user/discord_guilds/list.html.twig', ['guilds' => $guilds]);
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

        return $this->render('user/discord_guilds/bot_check.html.twig', ['guild' => $guild]);
    }
}
