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
use App\Repository\EventAttendeeRepository;
use App\Repository\EventRepository;
use App\Service\GuildLoggerService;
use Doctrine\ORM\EntityManagerInterface;

class UnattendBotCommand implements BotCommandInterface
{
    private EventRepository $eventRepository;
    private EventAttendeeRepository $eventAttendeeRepository;
    private EntityManagerInterface $entityManager;
    private GuildLoggerService $guildLoggerService;

    public function __construct(
        EventRepository $eventRepository,
        EventAttendeeRepository $eventAttendeeRepository,
        EntityManagerInterface $entityManager,
        GuildLoggerService $guildLoggerService
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventAttendeeRepository = $eventAttendeeRepository;
        $this->entityManager = $entityManager;
        $this->guildLoggerService = $guildLoggerService;
    }

    public function handle(DiscordRequest $request): DiscordResponse
    {
        $event = $this->eventRepository->find(trim($request->getArgs()));
        $response = new DiscordResponse();

        if (null === $event || $event->getGuild()->getId() !== $request->getGuild()->getId()) {
            $response->setContent('I don\'t know that event.');
        } else {
            $attendee = $this->eventAttendeeRepository->findOneBy(['user' => $request->getUser(), 'event' => $event]);
            if (null !== $attendee) {
                $this->guildLoggerService->eventUnattending($request->getGuild(), $event, $attendee);
                $this->entityManager->remove($attendee);
                if (null !== $event->getPoll()) {
                    foreach ($event->getPoll()->getVotes() as $vote) {
                        if ($vote->getUser()->getId() === $attendee->getUser()->getId()) {
                            $this->entityManager->remove($vote);
                        }
                    }
                }
                $this->entityManager->flush();
            }
            $response->setContent($request->getUser()->getDiscordMention().' you are no longer attending '.$event->getName());
        }

        return $response;
    }
}
