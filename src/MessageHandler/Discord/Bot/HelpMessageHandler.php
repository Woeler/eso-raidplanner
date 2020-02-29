<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\MessageHandler\Discord\Bot;

use App\Message\Discord\Bot\HelpCommandMessage;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use App\Utility\EsoClassUtility;
use App\Utility\EsoRoleUtility;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Woeler\DiscordPhp\Message\DiscordEmbedsMessage;

class HelpMessageHandler implements MessageHandlerInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var DiscordBotService
     */
    private $discordBotService;

    public function __construct(UserRepository $userRepository, DiscordBotService $discordBotService)
    {
        $this->userRepository = $userRepository;
        $this->discordBotService = $discordBotService;
    }

    public function __invoke(HelpCommandMessage $message)
    {
        $user = $this->userRepository->findOneBy(['discordId' => $message->getRequestData()['userId']]);
        $discordMessage = (new DiscordEmbedsMessage())
            ->addField('List all events', '!events')
            ->addField('Show specific event', '!event [eventID]'.PHP_EOL.'**Example**: `!event 1`')
            ->addField('Attend event', '!attend [eventId] [class] [role]'.PHP_EOL.'**Example**: `!attend 1 dragonknight tank`')
            ->addField('Leave event', '!unattend [eventId]'.PHP_EOL.'**Example**: `!unattend 1`')
            ->addField('See your character presets', '`!characters`')
            ->addField('Change your preferred timezone', '!timezone [timezone]')
            ->addField('List of valid timezones', '[Click here](https://www.php.net/manual/en/timezones.php)')
            ->addField('Usable classes', implode(', ', EsoClassUtility::toArray()))
            ->addField('Usable roles', implode(', ', EsoRoleUtility::toArray()));
        $discordMessage->setContent($user->getDiscordMention());

        $this->discordBotService->sendMessage($message->getChannelId(), $discordMessage);
    }
}
