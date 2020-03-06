<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Command;

use App\Entity\DiscordChannel;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Repository\EventRepository;
use App\Service\DiscordBotService;
use App\Service\ReminderService;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TriggerRemindersCommand extends Command
{
    protected static $defaultName = 'reminders:trigger';

    /**
     * @var DiscordBotService
     */
    private $discordBotService;

    /**
     * @var ReminderService
     */
    private $reminderService;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        DiscordBotService $discordBotService,
        ReminderService $reminderService,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->discordBotService = $discordBotService;
        $this->reminderService = $reminderService;
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
                    try {
                        $this->discordBotService->sendMessage(
                            null !== $event->getReminderRerouteChannel() ? $event->getReminderRerouteChannel()->getId() : $channel->getId(),
                            $message
                        );
                        $channel->setError(DiscordChannel::ERROR_NONE);
                    } catch (UnexpectedDiscordApiResponseException $e) {
                        if (false !== strpos($e->getMessage(), '404')) {
                            $channel->setError(DiscordChannel::ERROR_NOT_FOUND);
                        } else {
                            $channel->setError(DiscordChannel::ERROR_MISSING_PERMISSIONS);
                        }
                    }
                    $this->entityManager->persist($channel);
                }
            }
        }

        $this->entityManager->flush();
    }
}
