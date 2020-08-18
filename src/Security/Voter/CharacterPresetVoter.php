<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Security\Voter;

use App\Entity\CharacterPreset;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CharacterPresetVoter extends Voter
{
    public const VIEW = 'view';

    public const UPDATE = 'update';

    public const DELETE = 'delete';

    protected function supports($attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW, self::UPDATE, self::DELETE], true)
            && $subject instanceof CharacterPreset;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
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

    public function canView(CharacterPreset $preset, User $user): bool
    {
        return $preset->getUser()->getId() === $user->getId();
    }

    public function canUpdate(CharacterPreset $preset, User $user): bool
    {
        return $preset->getUser()->getId() === $user->getId();
    }

    public function canDelete(CharacterPreset $preset, User $user): bool
    {
        return $preset->getUser()->getId() === $user->getId();
    }
}
