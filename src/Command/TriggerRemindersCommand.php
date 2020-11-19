<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Command;

use App\Message\DiscordMessage;
use App\Repository\EventRepository;
use App\Service\ReminderService;
use DateTime;
use DateTimeZone;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TriggerRemindersCommand extends Command
{
    protected static $defaultName = 'reminders:trigger';

    private ReminderService $reminderService;
    private EventRepository $eventRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        ReminderService $reminderService,
        EventRepository $eventRepository,
        MessageBusInterface $messageBus
    ) {
        parent::__construct();
        $this->reminderService = $reminderService;
        $this->eventRepository = $eventRepository;
        $this->messageBus = $messageBus;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $events = $this->eventRepository->findFutureEvents($now);

        foreach ($events as $event) {
            $reminders = $event->getGuild()->getReminders();

            $diff = $now->diff($event->getStart());
            $minutes = $diff->i + (60 * $diff->h) + (24 * 60 * $diff->days) + 1;

            foreach ($reminders as $reminder) {
                if ($minutes === $reminder->getMinutesToTrigger()) {
                    $this->reminderService->setEvent($event);
                    $message = $this->reminderService->getDiscordMessage($reminder);
                    $channel = $reminder->getChannel();
                    if (null === $channel) {
                        continue;
                    }
                    $this->messageBus->dispatch(new DiscordMessage(
                        null !== $event->getReminderRerouteChannel() ? $event->getReminderRerouteChannel()->getId() : $channel->getId(),
                        $message->formatForDiscord()
                    ));
                }
            }
        }

        return 0;
    }
}
