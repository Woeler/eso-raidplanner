<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\MessageHandler\Discord\Bot;

use App\Entity\EventAttendee;
use App\Message\Discord\Bot\AttendCommandMessage;
use App\Repository\CharacterPresetRepository;
use App\Repository\DiscordGuildRepository;
use App\Repository\EventAttendeeRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use App\Service\GuildLoggerService;
use App\Utility\EsoClassUtility;
use App\Utility\EsoRoleUtility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AttendCommandHandler implements MessageHandlerInterface
{
    /**
     * @var DiscordGuildRepository
     */
    private $discordGuildRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var CharacterPresetRepository
     */
    private $characterPresetRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DiscordBotService
     */
    private $discordBotService;

    /**
     * @var EventAttendeeRepository
     */
    private $eventAttendeeRepository;

    /**
     * @var GuildLoggerService
     */
    private $guildLoggerService;

    public function __construct(
        DiscordGuildRepository $discordGuildRepository,
        UserRepository $userRepository,
        EventRepository $eventRepository,
        CharacterPresetRepository $characterPresetRepository,
        EntityManagerInterface $entityManager,
        DiscordBotService $discordBotService,
        EventAttendeeRepository $eventAttendeeRepository,
        GuildLoggerService $guildLoggerService
    ) {
        $this->discordGuildRepository = $discordGuildRepository;
        $this->userRepository = $userRepository;
        $this->eventRepository = $eventRepository;
        $this->characterPresetRepository = $characterPresetRepository;
        $this->entityManager = $entityManager;
        $this->discordBotService = $discordBotService;
        $this->eventAttendeeRepository = $eventAttendeeRepository;
        $this->guildLoggerService = $guildLoggerService;
    }

    public function __invoke(AttendCommandMessage $message)
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $message->getRequestData()['guildId']]);
        $exploded = explode(' ', trim($message->getRequestData()['query']));
        $event = $this->eventRepository->find($exploded[0]);
        unset($exploded[0]);
        $user = $this->userRepository->findOneBy(['discordId' => $message->getRequestData()['userId']]);

        $preset = $this->characterPresetRepository->findOneBy(
            [
                'name' => implode(' ', $exploded),
                'user' => $user,
            ]
        );
        if (null === $preset) {
            $class = EsoClassUtility::getClassIdByAlias($exploded[1] ?? '');
            $role = EsoRoleUtility::getRoleIdByAlias($exploded[2] ?? '');
            $sets = [];
        } else {
            $role = $preset->getRole();
            $class = $preset->getClass();
            $sets = $preset->getSets()->toArray();
        }

        if (null === $event || $event->getGuild()->getId() !== $guild->getId()) {
            $this->discordBotService->sendTextMessage(
                $message->getChannelId(),
                $user->getDiscordMention().' I don\'t know that event.'
            );

            return;
        } elseif (null === $class) {
            $this->discordBotService->sendTextMessage(
                $message->getChannelId(),
                $user->getDiscordMention().' I don\'t know that class.'
            );

            return;
        } elseif (null === $role) {
            $this->discordBotService->sendTextMessage(
                $message->getChannelId(),
                $user->getDiscordMention().' I don\'t know that role.'
            );

            return;
        }

        $attendee = $this->eventAttendeeRepository->findOneBy(['user' => $user, 'event' => $event]);
        $oldRole = null;
        if (null === $attendee) {
            $attendee = (new EventAttendee())
                ->setUser($user)
                ->setEvent($event);
        } else {
            $oldRole = $attendee->getRole();
        }
        $attendee->setClass($class)
            ->setRole($role)
            ->setSets($sets);
        if ($oldRole !== $attendee->getRole()) {
            $attendee->setStatus(EventAttendee::STATUS_ATTENDING);
        }

        $this->entityManager->persist($attendee);
        $this->entityManager->flush();

        $this->discordBotService->sendTextMessage(
            $message->getChannelId(),
            $user->getDiscordMention().' you are now attending '.$event->getName().' as a '.EsoClassUtility::getClassName($class).' '.EsoRoleUtility::getRoleName($role)
        );
        $this->guildLoggerService->eventAttending($guild, $event, $attendee);
    }
}
