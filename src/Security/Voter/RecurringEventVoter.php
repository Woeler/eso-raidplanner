<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Security\Voter;

use App\Entity\RecurringEvent;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RecurringEventVoter extends Voter
{
    public const DELETE = 'delete';
    public const VIEW = 'view';
    public const UPDATE = 'update';

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::VIEW, self::UPDATE], true)
            && $subject instanceof RecurringEvent;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::UPDATE:
                return $this->canUpdate($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        return false;
    }

    private function canView(RecurringEvent $event, User $user): bool
    {
        return $event->getGuild()->isAdmin($user);
    }

    private function canUpdate(RecurringEvent $event, User $user): bool
    {
        return $event->getGuild()->isAdmin($user);
    }

    private function canDelete(RecurringEvent $event, User $user): bool
    {
        return $event->getGuild()->isAdmin($user);
    }
}
