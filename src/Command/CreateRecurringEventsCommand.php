<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Command;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\RecurringEventRepository;
use App\Service\GuildLoggerService;
use Doctrine\ORM\EntityManagerInterface;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRecurringEventsCommand extends Command
{
    protected static $defaultName = 'recurring:create';

    /**
     * @var RecurringEventRepository
     */
    private $recurringEventRepository;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GuildLoggerService
     */
    private $guildLoggerService;

    public function __construct(
        RecurringEventRepository $recurringEventRepository,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager,
        GuildLoggerService $guildLoggerService
    ) {
        parent::__construct();
        $this->recurringEventRepository = $recurringEventRepository;
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
        $this->guildLoggerService = $guildLoggerService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates recurring events.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->recurringEventRepository->findAll() as $recurringEvent) {
            $events = count($this->eventRepository->findFutureEventsByRecurring($recurringEvent));
            $createInAdvance = $recurringEvent->getCreateInAdvanceAmount() - $events + 1;

            if (1 > $createInAdvance) {
                continue;
            }

            $startDate = new \DateTime(
                $recurringEvent->getLastEventStartDate()->format('Y-m-d H:i:s'),
                new \DateTimeZone('UTC')
            );
            $startDate->setTimezone(new \DateTimeZone($recurringEvent->getTimezone()));
            $rule = new Rule(
                'FREQ=WEEKLY;COUNT='.$createInAdvance.
                ';INTERVAL='.$recurringEvent->getWeekInterval().
                ';BYDAY='.implode(',', $recurringEvent->getDays()),
                $startDate,
                null,
                $recurringEvent->getTimezone()
            );

            $transformer = new ArrayTransformer();
            $lastDate = null;

            foreach ($transformer->transform($rule) as $newDate) {
                $start = $newDate->getStart();
                $start->setTimezone(new \DateTimeZone('UTC'));
                if ($recurringEvent->getLastEventStartDate()->getTimestamp() === $start->getTimestamp()) {
                    continue;
                }
                $event = $this->eventRepository->findOneBy(
                    [
                        'guild' => $recurringEvent->getGuild(),
                        'start' => $start,
                        'recurringParent' => $recurringEvent,
                    ]
                );

                if (null !== $event) {
                    continue;
                }

                $event = (new Event())
                    ->setName($recurringEvent->getName())
                    ->setGuild($recurringEvent->getGuild())
                    ->setDescription($recurringEvent->getDescription())
                    ->setStart($start)
                    ->setRecurringParent($recurringEvent);

                $this->entityManager->persist($event);
                $this->entityManager->flush();
                $this->guildLoggerService->eventCreated($event->getGuild(), $event);

                if (null === $lastDate || $lastDate->getTimestamp() < $event->getStart()->getTimestamp()) {
                    $lastDate = $event->getStart();
                }
            }

            if (null !== $lastDate) {
                $lastDate->setTimezone(new \DateTimeZone('UTC'));
                $recurringEvent->setLastEventStartDate($lastDate);
                $this->entityManager->persist($recurringEvent);
                $this->entityManager->flush();
            }
        }
    }
}
