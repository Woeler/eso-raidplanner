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

class GuildVoter extends Voter
{
    public const VIEW = 'view';
    public const VIEW_MEMBERS = 'view_members';
    public const VIEW_PAST_EVENTS = 'view_past_events';
    public const VIEW_SETTINGS = 'view_settings';
    public const CREATE_REMINDER = 'create_reminder';
    public const CREATE_EVENT = 'create_event';
    public const CREATE_RECURRING_EVENT = 'create_recurring_event';
    public const SYNC_DISCORD_CHANNELS = 'sync_discord_channels';
    public const PROMOTE = 'promote';
    public const DEMOTE = 'demote';
    public const DEACTIVATE = 'deactivate';
    public const UPDATE_NICKNAME = 'update_nickname';
    public const REMOVE_MEMBER = 'remove_member';

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [
            self::VIEW_SETTINGS, self::VIEW, self::VIEW_MEMBERS, self::CREATE_EVENT, self::CREATE_REMINDER, self::SYNC_DISCORD_CHANNELS, self::PROMOTE, self::DEMOTE, self::CREATE_RECURRING_EVENT, self::DEACTIVATE, self::UPDATE_NICKNAME, self::REMOVE_MEMBER, self::VIEW_PAST_EVENTS,
            ], true)
            && $subject instanceof DiscordGuild;
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
            case self::VIEW_MEMBERS:
                return $this->canViewMembers($subject, $user);
            case self::VIEW_SETTINGS:
                return $this->canViewSettings($subject, $user);
            case self::CREATE_REMINDER:
                return $this->canCreateReminder($subject, $user);
            case self::CREATE_EVENT:
                return $this->canCreateEvent($subject, $user);
                case self::CREATE_RECURRING_EVENT:
                return $this->canCreateRecurringEvent($subject, $user);
            case self::SYNC_DISCORD_CHANNELS:
                return $this->canSyncDiscordChannels($subject, $user);
            case self::PROMOTE:
                return $this->canPromote($subject, $user);
            case self::DEMOTE:
                return $this->canDemote($subject, $user);
            case self::DEACTIVATE:
                return $this->canDeactivate($subject, $user);
            case self::UPDATE_NICKNAME:
                return $this->canUpdateNickname($subject, $user);
            case self::REMOVE_MEMBER:
                return $this->canRemoveMember($subject, $user);
            case self::VIEW_PAST_EVENTS:
                return $this->canViewPastEvents($subject, $user);
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

    public function canCreateRecurringEvent(DiscordGuild $guild, User $user): bool
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

    public function canDeactivate(DiscordGuild $guild, User $user): bool
    {
        return $guild->getOwner()->getId() === $user->getId();
    }

    public function canUpdateNickname(DiscordGuild $guild, User $user): bool
    {
        return $guild->isMember($user);
    }

    public function canRemoveMember(DiscordGuild $guild, User $user): bool
    {
        return $guild->getOwner()->getId() === $user->getId();
    }

    public function canViewPastEvents(DiscordGuild $guild, User $user): bool
    {
        return $guild->isMember($user);
    }
}
