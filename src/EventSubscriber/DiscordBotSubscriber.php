<?php declare(strict_types=1);

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
use App\Repository\GuildMembershipRepository;
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
    private DiscordBotService $discordBotService;

    private DiscordGuildRepository $guildRepository;

    private UserRepository $userRepository;

    private string $token;

    private EntityManagerInterface $entityManager;

    private array $discordBotCommands;

    private array $defaultRoles;

    private GuildMembershipRepository $guildMembershipRepository;

    public function __construct(
        DiscordBotService $discordBotService,
        DiscordGuildRepository $guildRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        GuildMembershipRepository $guildMembershipRepository,
        string $token,
        array $discordBotCommands,
        array $defaultRoles
    ) {
        $this->discordBotService = $discordBotService;
        $this->guildRepository = $guildRepository;
        $this->userRepository = $userRepository;
        $this->token = $token;
        $this->entityManager = $entityManager;
        $this->discordBotCommands = $discordBotCommands;
        $this->defaultRoles = $defaultRoles;
        $this->guildMembershipRepository = $guildMembershipRepository;
    }

    public function onTalksWithDiscordController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof TalksWithDiscordBotController) {
            $token = str_replace('Basic ', '', $event->getRequest()->headers->get('Authorization') ?? '');
            if ($this->token !== base64_decode($token)) {
                throw new UnauthorizedHttpException('Invalid token');
            }

            if ('entryPoint' === $controller[1]) {
                $json = json_decode($event->getRequest()->getContent(), true, 512, JSON_THROW_ON_ERROR);
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
                        $user->getDiscordMention() . ' I do not know this guild.',
                        $channelId
                    );
                    throw new PreconditionFailedHttpException('Guild unknown');
                } elseif (!$guild->isActive()) {
                    $this->replyWithText(
                        $user->getDiscordMention() . ' I know this guild, but the owner has not activated it yet.',
                        $channelId
                    );
                    throw new PreconditionFailedHttpException('Guild inactive');
                } elseif (null === $user) {
                    $userInfo = $this->discordBotService->getUser($userId);
                    $user = (new User())
                        ->setDiscordId($userId)
                        ->setUsername($userInfo['username'])
                        ->setDiscordDiscriminator($userInfo['discriminator'])
                        ->setAvatar($userInfo['avatar'] ?? 'unknown')
                        ->setRoles($this->defaultRoles);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    $membership = (new GuildMembership())
                        ->setGuild($guild)
                        ->setUser($user)
                        ->setRole(GuildMembership::ROLE_MEMBER);
                    $this->entityManager->persist($membership);
                    $this->entityManager->flush();
                    try {
                        $this->replyWithDm(
                            $user->getDiscordMention() . ' Welcome to ESO Raidplanner. This is your first time interacting with the system, so we have configured some basic things for you. You should be all set to use ESO Raidplanner in this guild now. Your timezone has been set to UTC by default. You may change this by using the `!timezone` command in your guild.',
                            $userId
                        );
                    } catch (UnexpectedDiscordApiResponseException $e) {
                        $this->replyWithText(
                            $user->getDiscordMention() . ' Welcome to ESO Raidplanner. This is your first time interacting with the system, so we have configured some basic things for you. You should be all set to use ESO Raidplanner in this guild now. Your timezone has been set to UTC by default. You may change this by using the `!timezone` command.',
                            $channelId
                        );
                    }
                } elseif (!$guild->isMember($user)) {
                    $membership = (new GuildMembership())
                        ->setGuild($guild)
                        ->setUser($user)
                        ->setRole(GuildMembership::ROLE_MEMBER);
                    $this->entityManager->persist($membership);
                    $this->entityManager->flush();
                    $this->replyWithText(
                        $user->getDiscordMention() . ' You were not yet a member of this guild on ESO Raidplanner, you have now been added to this guild.',
                        $channelId
                    );
                }
                if (isset($json['userNick'])) {
                    $membership = $this->guildMembershipRepository->findOneBy(['guild' => $guild, 'user' => $user]);
                    if (null !== $membership) {
                        $membership->setNickname(
                            urldecode($json['userNick']) === $user->getUsername() ? null : urldecode($json['userNick'])
                        );
                        $this->entityManager->persist($membership);
                        $this->entityManager->flush();
                    }
                }
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

    /**
     * @param string $text
     * @param string $userId
     * @throws UnexpectedDiscordApiResponseException
     */
    protected function replyWithDm(string $text, string $userId): void
    {
        $message = new DiscordTextMessage();
        $message->setContent($text);

        $this->discordBotService->sendDirectMessage($userId, $message);
    }
}
