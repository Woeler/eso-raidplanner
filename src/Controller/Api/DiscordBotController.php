<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller\Api;

use App\Controller\Checks\TalksWithDiscordBotController;
use App\DTO\DiscordResponse;
use App\Entity\DiscordGuild;
use App\Entity\GuildMembership;
use App\Entity\User;
use App\Factory\DiscordRequestFactory;
use App\Repository\DiscordGuildRepository;
use App\Repository\GuildMembershipRepository;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/discord", name="api_discord_")
 */
class DiscordBotController extends AbstractController implements TalksWithDiscordBotController
{
    private const ALIASES = [
        'signup' => 'attend',
        'signoff' => 'unattend',
        'presets' => 'characters',
    ];

    private DiscordRequestFactory $requestFactory;
    private DiscordBotService $discordBotService;
    private EntityManagerInterface $entityManager;
    private array $defaultRoles;
    private iterable $commands;

    public function __construct(
        DiscordRequestFactory $requestFactory,
        DiscordBotService $discordBotService,
        EntityManagerInterface $entityManager,
        array $defaultRoles,
        iterable $commands
    ) {
        $this->requestFactory = $requestFactory;
        $this->discordBotService = $discordBotService;
        $this->entityManager = $entityManager;
        $this->defaultRoles = $defaultRoles;
        $this->commands = $commands;
    }

    /**
     * @Route("/bot", name="bot_entry_point", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function entryPoint(Request $request): Response
    {
        try {
            $discordRequest = $this->requestFactory->fromRequest($request);
        } catch (\InvalidArgumentException $e) {
            if (DiscordRequestFactory::GUILD_NOT_FOUND === $e->getCode()) {
                return JsonResponse::create((new DiscordResponse())
                    ->setContent('I do not know this guild.')
                    ->setOnlyText(true)
                    ->jsonSerialize(), Response::HTTP_OK);
            } elseif (DiscordRequestFactory::USER_NOT_FOUND === $e->getCode()) {
                $this->newUser(json_decode((string)$request->getContent(), true, 512, JSON_THROW_ON_ERROR)['userId']);
                $discordRequest = $this->requestFactory->fromRequest($request);
            } else {
                return Response::create('', Response::HTTP_BAD_REQUEST);
            }
        }

        if (!$discordRequest->getGuild()->isMember($discordRequest->getUser())) {
            $this->newMembership($discordRequest->getUser(), $discordRequest->getGuild());
        }

        $commandString = self::ALIASES[$discordRequest->getCommand()] ?? $discordRequest->getCommand();
        $class = 'App\BotCommand\\'.ucfirst($commandString ?? '').'BotCommand';

        foreach ($this->commands as $command) {
            if (get_class($command) === $class) {
                return JsonResponse::create($command->handle($discordRequest)->jsonSerialize(), Response::HTTP_OK);
            }
        }

        return Response::create('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/nickname", name="bot_nickname", methods={"POST"})
     *
     * @param Request $request
     * @param GuildMembershipRepository $guildMembershipRepository
     * @param DiscordGuildRepository $guildRepository
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function nicknameChange(
        Request $request,
        GuildMembershipRepository $guildMembershipRepository,
        DiscordGuildRepository $guildRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!array_key_exists('userId', $json) || !array_key_exists('guildId', $json) || !array_key_exists('userNick', $json)) {
            return Response::create('Missing parameters', Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['discordId' => $json['userId']]);
        $guild = $guildRepository->findOneBy(['id' => $json['guildId']]);

        if (null === $user || null === $guild) {
            return Response::create('Missing entities', Response::HTTP_BAD_REQUEST);
        }

        $membership = $guildMembershipRepository->findOneBy(['guild' => $guild, 'user' => $user]);

        if (null !== $membership) {
            $membership->setNickname(urldecode($json['userNick']) === $user->getUsername() ? null : urldecode($json['userNick']));
            $entityManager->persist($membership);
            $entityManager->flush();
        }

        return Response::create('ok', Response::HTTP_OK);
    }

    private function newUser(string $userId): void
    {
        $userInfo = $this->discordBotService->getUser($userId);
        $user = (new User())
            ->setDiscordId($userId)
            ->setUsername($userInfo['username'])
            ->setDiscordDiscriminator($userInfo['discriminator'])
            ->setAvatar($userInfo['avatar'] ?? 'unknown')
            ->setRoles($this->defaultRoles);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function newMembership(User $user, DiscordGuild $guild): void
    {
        $membership = (new GuildMembership())
            ->setGuild($guild)
            ->setUser($user)
            ->setRole(GuildMembership::ROLE_MEMBER);
        $this->entityManager->persist($membership);
        $this->entityManager->flush();
    }
}
