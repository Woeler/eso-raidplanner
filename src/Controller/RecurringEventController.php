<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Entity\Event;
use App\Entity\RecurringEvent;
use App\Form\RecurringEventType;
use App\Repository\DiscordGuildRepository;
use App\Repository\RecurringEventRepository;
use App\Security\Voter\GuildVoter;
use App\Security\Voter\RecurringEventVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/guild", name="guild_")
 */
class RecurringEventController extends AbstractController
{
    /**
     * @var DiscordGuildRepository
     */
    private $discordGuildRepository;

    /**
     * @var RecurringEventRepository
     */
    private $recurringEventRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        DiscordGuildRepository $discordGuildRepository,
        RecurringEventRepository $recurringEventRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->discordGuildRepository = $discordGuildRepository;
        $this->recurringEventRepository = $recurringEventRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/{guildId}/recurring", name="recurring_list")
     *
     * @param string $guildId
     * @param RecurringEventRepository $recurringEventRepository
     * @return Response
     */
    public function recurringEvents(string $guildId): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::CREATE_RECURRING_EVENT, $guild);
        $events = $this->recurringEventRepository->findBy(['guild' => $guild]);

        return $this->render(
            'recurring_event/list.html.twig',
            [
                'events' => $events,
                'guild' => $guild,
            ]
        );
    }

    /**
     * @Route("/{guildId}/recurring/create", name="recurring_create")
     *
     * @param Request $request
     * @param string $guildId
     * @return Response
     * @throws \Exception
     */
    public function createRecurringEvent(Request $request, string $guildId): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::CREATE_RECURRING_EVENT, $guild);
        $recurringEvent = new RecurringEvent();
        $form = $this->createForm(RecurringEventType::class, $recurringEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recurringEvent->setGuild($guild)
                ->setLastEventStartDate($recurringEvent->getDate());
            if (in_array(strtoupper(substr($recurringEvent->getDate()->format('D'), 0, 2)), $recurringEvent->getDays(), true)) {
                $this->entityManager->persist($recurringEvent);
                $this->entityManager->flush();

                $eventDate = new \DateTime($recurringEvent->getDate()->format('Y-m-d H:i:s'), new \DateTimeZone($recurringEvent->getTimezone()));
                $eventDate->setTimezone(new \DateTimeZone('UTC'));
                $event = (new Event())
                    ->setName($recurringEvent->getName())
                    ->setDescription($recurringEvent->getDescription())
                    ->setStart($eventDate)
                    ->setGuild($guild)
                    ->setRecurringParent($recurringEvent);
                $this->entityManager->persist($event);
                $this->entityManager->flush();

                $this->addFlash('success', 'Reminder '.$recurringEvent->getName().' created.');

                return $this->redirectToRoute('guild_recurring_list', ['guildId' => $guildId]);
            } else {
                $this->addFlash('danger', 'The start date you selected does not correspond with the weekdays you selected.');
            }
        }

        return $this->render(
            'recurring_event/form.html.twig',
            [
                'form' => $form->createView(),
                'guild' => $guild,
            ]
        );
    }

    /**
     * @Route("/{guildId}/recurring/{recurringEventId}/delete", name="recurring_delete")
     *
     * @param string $guildId
     * @param int $recurringEventId
     * @return Response
     */
    public function deleteRecurringEvent(string $guildId, int $recurringEventId): Response
    {
        $event = $this->recurringEventRepository->find($recurringEventId);
        $this->denyAccessUnlessGranted(RecurringEventVoter::DELETE, $event);
        $this->entityManager->remove($event);
        $this->entityManager->flush();
        $this->addFlash('success', 'Recurring event deleted.');

        return $this->redirectToRoute('guild_recurring_list', ['guildId' => $guildId]);
    }
}
