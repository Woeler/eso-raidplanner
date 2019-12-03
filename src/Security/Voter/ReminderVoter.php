<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Security\Voter;

use App\Entity\Reminder;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ReminderVoter extends Voter
{
    public const UPDATE = 'update';

    public const DELETE = 'delete';

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::UPDATE, self::DELETE], true)
            && $subject instanceof Reminder;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::UPDATE:
                return $this->canUpdate($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        return false;
    }

    public function canUpdate(Reminder $reminder, User $user): bool
    {
        return $reminder->getGuild()->isAdmin($user);
    }

    public function canDelete(Reminder $reminder, User $user): bool
    {
        return $reminder->getGuild()->isAdmin($user);
    }
}
