<?php declare(strict_types=1);

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
    private DiscordGuildRepository $discordGuildRepository;

    private UserRepository $userRepository;

    private EventRepository $eventRepository;

    private CharacterPresetRepository $characterPresetRepository;

    private EntityManagerInterface $entityManager;

    private DiscordBotService $discordBotService;

    private EventAttendeeRepository $eventAttendeeRepository;

    private GuildLoggerService $guildLoggerService;

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

    public function __invoke(AttendCommandMessage $message): void
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $message->getRequestData()['guildId']]);
        $user = $this->userRepository->findOneBy(['discordId' => $message->getRequestData()['userId']]);

        if (null === $user || null === $guild) {
            return;
        }

        $exploded = explode(' ', trim($message->getRequestData()['query']));
        $event = $this->eventRepository->find($exploded[0]);
        unset($exploded[0]);

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
        if (null !== $preset) {
            $attendee->setCharacterPreset($preset);
        }
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
