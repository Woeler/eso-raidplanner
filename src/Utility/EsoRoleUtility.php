<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Utility;

class EsoRoleUtility
{
    public const ROLE_TANK = 1;

    public const ROLE_HEALER = 2;

    public const ROLE_MAGICKA_DD = 3;

    public const ROLE_STAMINA_DD = 4;

    public const ROLE_OTHER = 5;

    private const ALL = [
        self::ROLE_TANK,
        self::ROLE_HEALER,
        self::ROLE_MAGICKA_DD,
        self::ROLE_STAMINA_DD,
        self::ROLE_OTHER,
    ];

    public static function getRoleName(int $roleId): string
    {
        switch ($roleId) {
            case self::ROLE_TANK:
                return 'Tank';
            case self::ROLE_HEALER:
                return 'Healer';
            case self::ROLE_MAGICKA_DD:
                return 'Magicka Damage Dealer';
            case self::ROLE_STAMINA_DD:
                return 'Stamina Damage Dealer';
            case self::ROLE_OTHER:
                return 'Other';
            default:
                return 'Unknown';
        }
    }

    public static function getRoleIcon(int $roleId): string
    {
        switch ($roleId) {
            case self::ROLE_TANK:
                return 'Tank';
            case self::ROLE_HEALER:
                return 'Healer';
            case self::ROLE_MAGICKA_DD:
                return 'Magicka Damage Dealer';
            case self::ROLE_STAMINA_DD:
                return 'Stamina Damage Dealer';
            case self::ROLE_OTHER:
                return 'Other';
            default:
                return 'Unknown';
        }
    }

    public static function toArray(): array
    {
        $array = [];
        foreach (self::ALL as $roleId) {
            $array[$roleId] = self::getRoleName($roleId);
        }

        return $array;
    }
}