<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    public const VIEW = 'view';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const ATTEND = 'attend';
    public const UNATTEND = 'unattend';
    public const CHANGE_ATTENDEE_STATUS = 'change_attendee_status';
    public const ATTEND_OTHER = 'attend_other';
    public const ADD_COMMENT = 'add_comment';

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::UNATTEND, self::UPDATE, self::DELETE, self::ATTEND, self::CHANGE_ATTENDEE_STATUS, self::ATTEND_OTHER, self::ADD_COMMENT], true)
            && $subject instanceof Event;
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
            case self::ATTEND:
                return $this->canAttend($subject, $user);
            case self::UNATTEND:
                return $this->canUnattend($subject, $user);
            case self::CHANGE_ATTENDEE_STATUS:
                return $this->canChangeAttendeeStatus($subject, $user);
            case self::ATTEND_OTHER:
                return $this->canAttendOther($subject, $user);
            case self::ADD_COMMENT:
                return $this->canAddComment($subject, $user);
        }

        return false;
    }

    private function canView(Event $event, User $user): bool
    {
        return $event->getGuild()->isMember($user);
    }

    private function canUpdate(Event $event, User $user): bool
    {
        return $event->getGuild()->isAdmin($user);
    }

    private function canDelete(Event $event, User $user): bool
    {
        return $event->getGuild()->isAdmin($user);
    }

    private function canAttend(Event $event, User $user): bool
    {
        return $event->getGuild()->isMember($user)
            && !$event->getLocked()
            && $event->getStart()->getTimestamp() > (new \DateTime())->getTimestamp();
    }

    private function canUnattend(Event $event, User $user): bool
    {
        return $event->getGuild()->isMember($user)
            && !$event->getLocked()
            && $event->getStart()->getTimestamp() > (new \DateTime())->getTimestamp();
    }

    private function canChangeAttendeeStatus(Event $event, User $user): bool
    {
        return $event->getGuild()->isAdmin($user);
    }

    private function canAttendOther(Event $event, User $user): bool
    {
        return $event->getGuild()->isAdmin($user);
    }

    public function canAddComment(Event $event, User $user): bool
    {
        return $event->getGuild()->isMember($user);
    }
}
