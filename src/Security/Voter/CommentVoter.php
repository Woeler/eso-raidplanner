<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Security\Voter;

use App\Entity\Comment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
{
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE], true)
            && $subject instanceof Comment;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        return false;
    }

    public function canEdit(Comment $comment, User $user): bool
    {
        return $user->getId() === $comment->getUser()->getId();
    }

    public function canDelete(Comment $comment, User $user): bool
    {
        return ($user->getId() === $comment->getUser()->getId()) || ($comment->getEvent()->getGuild()->isAdmin($user));
    }
}
