<?php
declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\BotCommand;

use App\DTO\DiscordRequest;
use App\DTO\DiscordResponse;
use App\Entity\EventAttendee;
use App\Repository\CharacterPresetRepository;
use App\Repository\EventAttendeeRepository;
use App\Repository\EventRepository;
use App\Service\GuildLoggerService;
use App\Utility\EsoClassUtility;
use App\Utility\EsoRoleUtility;
use Doctrine\ORM\EntityManagerInterface;

class AttendBotCommand implements BotCommandInterface
{
    private EventRepository $eventRepository;
    private CharacterPresetRepository $characterPresetRepository;
    private EntityManagerInterface $entityManager;
    private EventAttendeeRepository $eventAttendeeRepository;
    private GuildLoggerService $guildLoggerService;

    public function __construct(
        EventRepository $eventRepository,
        CharacterPresetRepository $characterPresetRepository,
        EntityManagerInterface $entityManager,
        EventAttendeeRepository $eventAttendeeRepository,
        GuildLoggerService $guildLoggerService
    ) {
        $this->eventRepository = $eventRepository;
        $this->characterPresetRepository = $characterPresetRepository;
        $this->entityManager = $entityManager;
        $this->eventAttendeeRepository = $eventAttendeeRepository;
        $this->guildLoggerService = $guildLoggerService;
    }

    public function handle(DiscordRequest $request): DiscordResponse
    {
        $response = new DiscordResponse();
        $exploded = explode(' ', trim($request->getArgs()));
        $event = $this->eventRepository->find($exploded[0]);
        unset($exploded[0]);

        $preset = $this->characterPresetRepository->findOneBy(
            [
                'name' => implode(' ', $exploded),
                'user' => $request->getUser(),
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

        if (null === $event || $event->getGuild()->getId() !== $request->getGuild()->getId()) {
            $response->setContent($request->getUser()->getDiscordMention().' I don\'t know that event.');
        } elseif (null === $class) {
            $response->setContent($request->getUser()->getDiscordMention().' I don\'t know that class.');
        } elseif (null === $role) {
            $response->setContent($request->getUser()->getDiscordMention().' I don\'t know that role.');
        } else {
            $attendee = $this->eventAttendeeRepository->findOneBy(['user' => $request->getUser(), 'event' => $event]);
            $oldRole = null;
            if (null === $attendee) {
                $attendee = (new EventAttendee())
                    ->setUser($request->getUser())
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

            $response->setContent($request->getUser()->getDiscordMention().' you are now attending '.$event->getName().' as a '.EsoClassUtility::getClassName($class).' '.EsoRoleUtility::getRoleName($role));
            $this->guildLoggerService->eventAttending($request->getGuild(), $event, $attendee);
        }

        return $response->setOnlyText(true);
    }
}
