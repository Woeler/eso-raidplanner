<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Utility;

class EsoClassUtility
{
    public const CLASS_DRAGONKNIGHT = 1;

    public const CLASS_SORCERER = 2;

    public const CLASS_NIGHTBLADE = 3;

    public const CLASS_WARDEN = 4;

    public const CLASS_NECROMANCER = 5;

    public const CLASS_TEMPLAR = 6;

    private const ALL = [
        self::CLASS_DRAGONKNIGHT,
        self::CLASS_SORCERER,
        self::CLASS_NIGHTBLADE,
        self::CLASS_WARDEN,
        self::CLASS_NECROMANCER,
        self::CLASS_TEMPLAR,
    ];

    private const ALIASES = [
        self::CLASS_DRAGONKNIGHT => ['dragonknight', 'dk', 'd'],
        self::CLASS_SORCERER => ['sorcerer', 'sorc', 's'],
        self::CLASS_NIGHTBLADE => ['nightblade', 'nb'],
        self::CLASS_WARDEN => ['warden', 'w'],
        self::CLASS_NECROMANCER => ['necromancer', 'necro'],
        self::CLASS_TEMPLAR => ['templar', 'temp', 't'],
    ];

    /**
     * @param int $classId
     * @return string
     */
    public static function getClassName(int $classId): string
    {
        switch ($classId) {
            case self::CLASS_DRAGONKNIGHT:
                return 'Dragonknight';
            case self::CLASS_SORCERER:
                return 'Sorcerer';
            case self::CLASS_NIGHTBLADE:
                return 'Nightblade';
            case self::CLASS_WARDEN:
                return 'Warden';
            case self::CLASS_NECROMANCER:
                return 'Necromancer';
            case self::CLASS_TEMPLAR:
                return 'Templar';
            default:
                return 'Unknown';
        }
    }

    /**
     * @param int $classId
     * @return string
     */
    public static function getClassIcon(int $classId): string
    {
        switch ($classId) {
            case self::CLASS_DRAGONKNIGHT:
                return 'build/images/classes/dragonknight.png';
            case self::CLASS_SORCERER:
                return 'build/images/classes/sorcerer.png';
            case self::CLASS_NIGHTBLADE:
                return 'build/images/classes/nightblade.png';
            case self::CLASS_WARDEN:
                return 'build/images/classes/warden.png';
            case self::CLASS_NECROMANCER:
                return 'build/images/classes/necromancer.png';
            case self::CLASS_TEMPLAR:
                return 'build/images/classes/templar.png';
            default:
                return 'build/images/classes/unknown.png';
        }
    }

    /**
     * @param int $classId
     * @return string
     */
    public static function getClassDiscordEmoji(int $classId): string
    {
        switch ($classId) {
            case self::CLASS_DRAGONKNIGHT:
                return '<:dragonknight:682336633348948022>';
            case self::CLASS_SORCERER:
                return '<:sorcerer:682336719454208005>';
            case self::CLASS_NIGHTBLADE:
                return '<:nightblade:682336718996635704>';
            case self::CLASS_WARDEN:
                return '<:warden:682336719390900227>';
            case self::CLASS_NECROMANCER:
                return '<:necromancer:682336719475048524>';
            case self::CLASS_TEMPLAR:
                return '<:templar:682336719424716934>';
            default:
                return '';
        }
    }

    /**
     * @return array
     */
    public static function toArray(): array
    {
        $array = [];
        foreach (self::ALL as $roleId) {
            $array[$roleId] = self::getClassName($roleId);
        }

        return $array;
    }

    /**
     * @param string $alias
     * @return int|null
     */
    public static function getClassIdByAlias(string $alias): ?int
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
