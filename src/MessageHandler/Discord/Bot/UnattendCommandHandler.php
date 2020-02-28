<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\MessageHandler\Discord\Bot;

use App\Message\Discord\Bot\UnattendCommandMessage;
use App\Repository\DiscordGuildRepository;
use App\Repository\EventAttendeeRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use App\Service\GuildLoggerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Woeler\DiscordPhp\Message\DiscordTextMessage;

class UnattendCommandHandler implements MessageHandlerInterface
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
     * @var EventAttendeeRepository
     */
    private $eventAttendeeRepository;

    /**
     * @var DiscordBotService
     */
    private $discordBotService;

    /**
     * @var GuildLoggerService
     */
    private $guildLoggerService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        DiscordGuildRepository $discordGuildRepository,
        EventRepository $eventRepository,
        UserRepository $userRepository,
        EventAttendeeRepository $eventAttendeeRepository,
        DiscordBotService $discordBotService,
        GuildLoggerService $guildLoggerService,
        EntityManagerInterface $entityManager
    ) {
        $this->discordGuildRepository = $discordGuildRepository;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->eventAttendeeRepository = $eventAttendeeRepository;
        $this->discordBotService = $discordBotService;
        $this->guildLoggerService = $guildLoggerService;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UnattendCommandMessage $message)
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $message->getRequestData()['guildId']]);
        $event = $this->eventRepository->find(trim($message->getRequestData()['query']));
        $user = $this->userRepository->findOneBy(['discordId' => $message->getRequestData()['userId']]);
        $discordMessage =  new DiscordTextMessage();
        if (null === $event || $event->getGuild()->getId() !== $guild->getId()) {
            $discordMessage->setContent('I don\'t know that event.');
        } else {
            $attendee = $this->eventAttendeeRepository->findOneBy(['user' => $user, 'event' => $event]);
            if (null !== $attendee) {
                $this->guildLoggerService->eventUnattending($guild, $event, $attendee);
                $this->entityManager->remove($attendee);
                $this->entityManager->flush();
            }
            $discordMessage->setContent($user->getDiscordMention().' you are no longer attending '.$event->getName());
        }

        $this->discordBotService->sendMessage($message->getChannelId(), $discordMessage);
    }
}
