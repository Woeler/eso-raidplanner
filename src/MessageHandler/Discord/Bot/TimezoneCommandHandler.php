<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\MessageHandler\Discord\Bot;

use App\Message\Discord\Bot\TimezoneCommandMessage;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use App\Utility\TimezoneUtility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Woeler\DiscordPhp\Message\DiscordTextMessage;

class TimezoneCommandHandler implements MessageHandlerInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DiscordBotService
     */
    private $discordBotService;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        DiscordBotService $discordBotService
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->discordBotService = $discordBotService;
    }

    public function __invoke(TimezoneCommandMessage $message)
    {
        $timezone = trim($message->getRequestData()['query']);
        $user = $this->userRepository->findOneBy(['discordId' => $message->getRequestData()['userId']]);

        $discordMessage = new DiscordTextMessage();
        if (array_key_exists($timezone, TimezoneUtility::timeZones())) {
            $user->setTimezone($timezone);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $discordMessage->setContent($user->getDiscordMention().' Your timezone has been set to '.$timezone.'.');
        } elseif (empty($timezone)) {
            $discordMessage->setContent(
                $user->getDiscordMention().' Please specify a timezone in your command. Here is an example `!timezone Europe/Berlin`, and here is a list of supported timezones https://www.php.net/manual/en/timezones.php'
            );
        } else {
            $discordMessage->setContent(
                $user->getDiscordMention() . ' I do not know the timezone ' . $timezone . '. Please make sure to check out this official timezone list. Also be aware that the timezone you give me is case sensitive. https://www.php.net/manual/en/timezones.php'
                . PHP_EOL . 'Here is an example `!timezone Europe/Berlin`'
            );
        }

        $this->discordBotService->sendMessage($message->getChannelId(), $discordMessage);
    }
}
