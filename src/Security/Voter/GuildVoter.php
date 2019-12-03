<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Security\Voter;

use App\Entity\DiscordGuild;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class GuildVoter extends Voter
{
    public const VIEW = 'view';

    public const VIEW_MEMBERS = 'view_members';

    public const VIEW_SETTINGS = 'view_settings';

    public const CREATE_REMINDER = 'create_reminder';

    public const CREATE_EVENT = 'create_event';

    public const SYNC_DISCORD_CHANNELS = 'sync_discord_channels';

    public const PROMOTE = 'promote';

    public const DEMOTE = 'demote';

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [
            self::VIEW_SETTINGS, self::VIEW, self::VIEW_MEMBERS, self::CREATE_EVENT, self::CREATE_REMINDER, self::SYNC_DISCORD_CHANNELS, self::PROMOTE, self::DEMOTE,
            ], true)
            && $subject instanceof \App\Entity\DiscordGuild;
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
            case self::VIEW_MEMBERS:
                return $this->canViewMembers($subject, $user);
            case self::VIEW_SETTINGS:
                return $this->canViewSettings($subject, $user);
            case self::CREATE_REMINDER:
                return $this->canCreateReminder($subject, $user);
            case self::CREATE_EVENT:
                return $this->canCreateEvent($subject, $user);
            case self::SYNC_DISCORD_CHANNELS:
                return $this->canSyncDiscordChannels($subject, $user);
            case self::PROMOTE:
                return $this->canPromote($subject, $user);
            case self::DEMOTE:
                return $this->canDemote($subject, $user);
        }

        return false;
    }

    private function canView(DiscordGuild $guild, User $user): bool
    {
        return $guild->isMember($user);
    }

    private function canViewMembers(DiscordGuild $guild, User $user): bool
    {
        return $guild->isMember($user);
    }

    private function canViewSettings(DiscordGuild $guild, User $user): bool
    {
        return $guild->isAdmin($user);
    }

    private function canCreateEvent(DiscordGuild $guild, User $user): bool
    {
        return $guild->isAdmin($user);
    }

    private function canCreateReminder(DiscordGuild $guild, User $user): bool
    {
        return $guild->isAdmin($user);
    }

    public function canSyncDiscordChannels(DiscordGuild $guild, User $user): bool
    {
        return $guild->isAdmin($user);
    }

    public function canPromote(DiscordGuild $guild, User $user): bool
    {
        return $guild->getOwner()->getId() === $user->getId();
    }

    public function canDemote(DiscordGuild $guild, User $user): bool
    {
        return $guild->getOwner()->getId() === $user->getId();
    }
}
