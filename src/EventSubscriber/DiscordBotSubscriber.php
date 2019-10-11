<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\EventSubscriber;

use App\Controller\Checks\TalksWithDiscordBotController;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Repository\DiscordGuildRepository;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
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

    public function __construct(
        DiscordBotService $discordBotService,
        DiscordGuildRepository $guildRepository,
        UserRepository $userRepository,
        string $token
    ) {
        $this->discordBotService = $discordBotService;
        $this->guildRepository = $guildRepository;
        $this->userRepository = $userRepository;
        $this->token = $token;
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
            $guildId = $event->getRequest()->get('guildId');
            $userId = $event->getRequest()->get('userId');
            $channelId = $event->getRequest()->get('channelId');
            $guild = $this->guildRepository->find($guildId);
            $user = $this->userRepository->findOneBy(['discordId' => $userId]);

            if (null === $user) {
                $this->replyWithText(
                    '<@'.$userId.'> I do not know you. You must at least log in once to ESO Raidplanner.',
                    $channelId
                );
                throw new PreconditionFailedHttpException('User unknown');
            } elseif (null === $guild) {
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
