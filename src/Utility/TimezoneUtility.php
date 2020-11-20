<?php
declare(strict_types = 1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Utility;

use DateTime;
use DateTimeZone;

class TimezoneUtility
{
    public static function timeZones(): array
    {
        static $regions = [
            DateTimeZone::AFRICA,
            DateTimeZone::AMERICA,
            DateTimeZone::ANTARCTICA,
            DateTimeZone::ASIA,
            DateTimeZone::ATLANTIC,
            DateTimeZone::AUSTRALIA,
            DateTimeZone::EUROPE,
            DateTimeZone::INDIAN,
            DateTimeZone::PACIFIC,
            DateTimeZone::UTC,
        ];

        $timezones = [];
        foreach ($regions as $region) {
            $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
        }

        $timezoneOffsets = [];
        foreach ($timezones as $timezone) {
            $tz                          = new DateTimeZone($timezone);
            $timezoneOffsets[$timezone] = $tz->getOffset(new DateTime());
        }

        // sort timezone by offset
        asort($timezoneOffsets);

        $timezoneList = [];
        foreach ($timezoneOffsets as $timezone => $offset) {
            $offsetPrefix    = 0 > $offset ? '-' : '+';
            $offsetFormatted = gmdate('H:i', abs($offset));

            $offsetPretty = 'UTC' . $offsetPrefix . $offsetFormatted;

            $timezoneList[$timezone] = '(' . $offsetPretty . ') ' . $timezone;
        }

        return $timezoneList;
    }
}
