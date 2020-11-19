<?php
declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\MessageHandler;

use App\Exception\UnexpectedDiscordApiResponseException;
use App\Message\DiscordMessage;
use App\Service\DiscordBotService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DiscordMessageHandler implements MessageHandlerInterface
{
    private DiscordBotService $discordBotService;
    private LoggerInterface $logger;

    public function __construct(DiscordBotService $discordBotService, LoggerInterface $logger)
    {
        $this->discordBotService = $discordBotService;
        $this->logger = $logger;
    }

    public function __invoke(DiscordMessage $message): void
    {
        try {
            $this->discordBotService->sendMessageWithArray($message->getChannelId(), $message->getData());
        } catch (UnexpectedDiscordApiResponseException $e) {
            $this->logger->error(
                'Could not send Discord message',
                [
                'exception' => $e->getMessage(),
                'channel' => $message->getChannelId(),
                'message' => $message->getData(),
            ]
            );
        }
    }
}
