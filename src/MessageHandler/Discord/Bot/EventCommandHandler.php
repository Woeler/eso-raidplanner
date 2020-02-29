<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\MessageHandler\Discord\Bot;

use App\Message\Discord\Bot\EventCommandMessage;
use App\Repository\DiscordGuildRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use App\Utility\EsoClassUtility;
use App\Utility\EsoRoleUtility;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Woeler\DiscordPhp\Message\DiscordEmbedsMessage;
use Woeler\DiscordPhp\Message\DiscordTextMessage;

class EventCommandHandler implements MessageHandlerInterface
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

    public function __invoke(EventCommandMessage $message)
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $message->getRequestData()['guildId']]);
        if (empty(trim($message->getRequestData()['query']))) {
            $event = $this->eventRepository->findFirstFutureEvent($guild);
        } else {
            $event = $this->eventRepository->find(trim($message->getRequestData()['query']));
        }
        $user = $this->userRepository->findOneBy(['discordId' => $message->getRequestData()['userId']]);
        if (null === $event || $event->getGuild()->getId() !== $guild->getId()) {
            $discordMessage = new DiscordTextMessage();
            $discordMessage->setContent($user->getDiscordMention().' I don\'t know that event.');
            $this->discordBotService->sendMessage($message->getChannelId(), $discordMessage);

            return;
        }

        $discordMessage = (new DiscordEmbedsMessage())
            ->setTitle($event->getName())
            ->setAuthorIcon('https://cdn.discordapp.com/icons/'.$guild->getId().'/'.$guild->getIcon().'.png')
            ->setAuthorName($guild->getName())
            ->setDescription($event->getDescription() ?? '')
            ->addField(
                'Date and Time',
                $user->toUserTimeString($event->getStart()).PHP_EOL.'(in your timezone: '.$user->getTimezone().')'
            );
        $discordMessage->setContent($user->getDiscordMention());
        foreach (EsoRoleUtility::toArray() as $roleId => $roleName) {
            $attendees = $event->getAttendeesByRole($roleId);
            if (0 < count($attendees)) {
                $text = '';
                foreach ($attendees as $attendee) {
                    $text .= trim($attendee->getStatusEmoji().' '
                            .$attendee->getUser()->getDiscordMention().' '
                            .EsoClassUtility::getClassDiscordEmoji($attendee->getClass())).PHP_EOL;
                }
                $discordMessage->addField($roleName, $text, true);
            }
        }
        $this->discordBotService->sendMessage($message->getChannelId(), $discordMessage);
    }
}
