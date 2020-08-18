<?php declare(strict_types=1);

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

    private const ALIASES = [
        self::ROLE_TANK => ['tank', 't'],
        self::ROLE_HEALER => ['healer', 'heals', 'heal', 'h'],
        self::ROLE_MAGICKA_DD => ['mdd', 'magdd', 'magicka', 'm'],
        self::ROLE_STAMINA_DD => ['sdd', 'stamdd', 'stamina', 's'],
        self::ROLE_OTHER => ['other', 'pvp', 'o'],
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
                return 'build/images/roles/tank.png';
            case self::ROLE_HEALER:
                return 'build/images/roles/healer.png';
            case self::ROLE_MAGICKA_DD:
                return 'build/images/roles/dd.png';
            case self::ROLE_STAMINA_DD:
                return 'build/images/roles/dd.png';
            case self::ROLE_OTHER:
                return 'build/images/roles/other.png';
            default:
                return 'build/images/roles/unknown.png';
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

    /**
     * @param string $alias
     * @return int|null
     */
    public static function getRoleIdByAlias(string $alias): ?int
    {
        $alias = strtolower(trim($alias));
        foreach (self::ALIASES as $id => $aliases) {
            foreach ($aliases as $al) {
                if ($alias === $al) {
                    return $id;
                }
            }
        }

        return null;
    }
}
