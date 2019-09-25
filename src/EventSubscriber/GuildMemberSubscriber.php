<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\EventSubscriber;

use App\Controller\Checks\GuildMemberCheckController;
use App\Entity\User;
use App\Repository\DiscordGuildRepository;
use App\Repository\EventRepository;
use App\Repository\ReminderRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GuildMemberSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var DiscordGuildRepository
     */
    private $discordGuildRepository;

    /**
     * This array contains all the actions in the GuildController
     * that require guild member status
     *
     * @var array
     */
    private $memberActions = [
        'view',
        'viewEvent',
        'eventUnattend',
        'members',
    ];

    /**
     * This array contains all the actions in the GuildController
     * that require guild admin status
     *
     * @var array
     */
    private $adminActions = [
        'createEvent',
        'updateEvent',
        'deleteEvent',
        'settings',
        'syncDiscordChannels',
        'createReminder',
        'updateReminder',
        'deleteReminder',
    ];

    /**
     * This array contains all the actions in the GuildController
     * that require guild owner status
     *
     * @var array
     */
    private $ownerActions = [
        'demoteToMember',
        'promoteToAdmin',
    ];

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var ReminderRepository
     */
    private $reminderRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        DiscordGuildRepository $discordGuildRepository,
        EventRepository $eventRepository,
        ReminderRepository $reminderRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->discordGuildRepository = $discordGuildRepository;
        $this->eventRepository = $eventRepository;
        $this->reminderRepository = $reminderRepository;
    }

    /**
     * @param ControllerEvent $event
     */
    public function onGuildMemberCheckController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof GuildMemberCheckController) {
            $guildId = $event->getRequest()->attributes->get('guildId');
            $guild = $this->discordGuildRepository->find($guildId);
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            if (null === $guild || null === $guildId || null === $user || is_string($user) || !$guild->isActive()) {
                throw new AccessDeniedHttpException('Invalid entities.');
            }

            // When an event is in the route check if it exists and if it belongs to this guild
            $eventId = $event->getRequest()->attributes->get('eventId');

            if (null !== $eventId) {
                $guildEvent = $this->eventRepository->find($eventId);
                if (null === $guildEvent || $guild->getId() !== $guildEvent->getGuild()->getId()) {
                    throw new NotFoundHttpException('This event/guild combination was not found');
                }
            }

            $reminderId = $event->getRequest()->attributes->get('reminderId');

            if (null !== $reminderId) {
                $guildReminder = $this->reminderRepository->find($reminderId);
                if (null === $guildReminder || $guild->getId() !== $guildReminder->getGuild()->getId()) {
                    throw new NotFoundHttpException('This reminder/guild combination was not found');
                }
            }

            // Member Actions
            if (in_array($controller[1], $this->memberActions, true)) {
                if (!$guild->isMember($user)) {
                    throw new AccessDeniedHttpException('You are not a member of this guild.');
                }
            }

            // Admin Actions
            if (in_array($controller[1], $this->adminActions, true)) {
                if (!$guild->isAdmin($user)) {
                    throw new AccessDeniedHttpException('You are not an admin of this guild.');
                }
            }

            // Owner Actions
            if (in_array($controller[1], $this->ownerActions, true)) {
                if ($guild->getOwner()->getId() !== $user->getId()) {
                    throw new AccessDeniedHttpException('You are not the owner of this guild.');
                }
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onGuildMemberCheckController',
        ];
    }
}
