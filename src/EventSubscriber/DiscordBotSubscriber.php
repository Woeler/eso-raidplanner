<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\EventSubscriber;

use App\Controller\Checks\TalksWithDiscordBotController;
use App\Entity\GuildMembership;
use App\Entity\User;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Repository\DiscordGuildRepository;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Woeler\DiscordPhp\Message\DiscordTextMessage;

class DiscordBotSubscriber implements EventSubscriberInterface
{
    /**
     * @var DiscordBotService
     */
    private $discordBotService;

    /**
     * @var DiscordGuildRepository
     */
    private $guildRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var string
     */
    private $token;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var array
     */
    private $discordBotCommands;

    public function __construct(
        DiscordBotService $discordBotService,
        DiscordGuildRepository $guildRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        string $token,
        array $discordBotCommands
    ) {
        $this->discordBotService = $discordBotService;
        $this->guildRepository = $guildRepository;
        $this->userRepository = $userRepository;
        $this->token = $token;
        $this->entityManager = $entityManager;
        $this->discordBotCommands = $discordBotCommands;
    }

    public function onTalksWithDiscordController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof TalksWithDiscordBotController) {
            $token = str_replace('Basic ', '', $event->getRequest()->headers->get('Authorization') ?? '');
            if ($this->token !== base64_decode($token)) {
                throw new UnauthorizedHttpException('Invalid token');
            }
            $json = json_decode($event->getRequest()->getContent(), true);
            if (!in_array($json['command'], $this->discordBotCommands, true)) {
                throw new PreconditionFailedHttpException('Unknown command');
            }
            $guildId = $json['guildId'];
            $userId = $json['userId'];
            $channelId = $json['channelId'];
            $guild = $this->guildRepository->find($guildId);
            $user = $this->userRepository->findOneBy(['discordId' => $userId]);

            if (null === $guild) {
                $this->replyWithText(
                    $user->getDiscordMention().' I do not know this guild.',
                    $channelId
                );
                throw new PreconditionFailedHttpException('Guild unknown');
            } elseif (!$guild->isActive()) {
                $this->replyWithText(
                    $user->getDiscordMention().' I know this guild, but the owner has not activated it yet.',
                    $channelId
                );
                throw new PreconditionFailedHttpException('Guild inactive');
            } elseif (null === $user) {
                $userInfo = $this->discordBotService->getUser($userId);
                $user = (new User())
                    ->setDiscordId($userId)
                    ->setUsername($userInfo['username'])
                    ->setDiscordDiscriminator($userInfo['discriminator'])
                    ->setAvatar($userInfo['avatar'] ?? 'unknown');
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $membership = (new GuildMembership())
                    ->setGuild($guild)
                    ->setUser($user)
                    ->setRole(GuildMembership::ROLE_MEMBER);
                $this->entityManager->persist($membership);
                $this->entityManager->flush();
                $this->replyWithText(
                    $user->getDiscordMention().' Welcome to ESO Raidplanner. This is your first time interacting with the system, so we have configured some basic things for you. You should be all set to use ESO Raidplanner in this guild now. Your timezone has been set to UTC by default. You may change this by using the `!timezone` command.',
                    $channelId
                );
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onTalksWithDiscordController',
        ];
    }

    /**
     * @param string $text
     * @param string $channelId
     * @throws UnexpectedDiscordApiResponseException
     */
    protected function replyWithText(string $text, string $channelId): void
    {
        $message = new DiscordTextMessage();
        $message->setContent($text);

        $this->discordBotService->sendMessage($channelId, $message);
    }
}
