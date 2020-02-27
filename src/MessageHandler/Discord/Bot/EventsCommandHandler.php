<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\MessageHandler\Discord\Bot;

use App\Message\Discord\Bot\EventsCommandMessage;
use App\Repository\DiscordGuildRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Woeler\DiscordPhp\Message\DiscordEmbedsMessage;

class EventsCommandHandler implements MessageHandlerInterface
{
    /**
     * @var DiscordGuildRepository
     */
    private $discordGuildRepository;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var DiscordBotService
     */
    private $discordBotService;

    public function __construct(
        DiscordGuildRepository $discordGuildRepository,
        EventRepository $eventRepository,
        UserRepository $userRepository,
        DiscordBotService $discordBotService
    ) {
        $this->discordGuildRepository = $discordGuildRepository;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->discordBotService = $discordBotService;
    }

    public function __invoke(EventsCommandMessage $message)
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $message->getRequestData()['guildId']]);
        $events = $this->eventRepository->findFutureEventsForGuild($guild);
        $user = $this->userRepository->findOneBy(['discordId' => $message->getRequestData()['userId']]);
        $desc = '';
        foreach ($events as $event) {
            $desc .= $event->getId().': **'.$event->getName().'**'.PHP_EOL.$user->toUserTimeString($event->getStart()).PHP_EOL.PHP_EOL;
        }

        $discordMessage = (new DiscordEmbedsMessage())
            ->setTitle('Upcoming events')
            ->setAuthorIcon('https://cdn.discordapp.com/icons/'.$guild->getId().'/'.$guild->getIcon().'.png')
            ->setAuthorName($guild->getName())
            ->addField('Times displayed in your timezone', $user->getTimezone())
            ->setDescription($desc)
            ->setFooterIcon('https://esoraidplanner.com/build/images/favicon/appicon.jpg')
            ->setFooterText('ESO Raidplanner by Woeler')
            ->setColor(9660137);
        $discordMessage->setContent($user->getDiscordMention());

        $this->discordBotService->sendMessage($message->getChannelId(), $discordMessage);
    }
}
