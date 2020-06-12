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
use Symfony\Component\Security\Core\User\UserInterface;

class RecurringEventVoter extends Voter
{
    public const DELETE = 'delete';

    public const VIEW = 'view';

    public const UPDATE = 'update';

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::DELETE, self::VIEW], true)
            && $subject instanceof RecurringEvent;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
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
