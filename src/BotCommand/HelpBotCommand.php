<?php
declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\BotCommand;

use App\DTO\DiscordRequest;
use App\DTO\DiscordResponse;
use App\Utility\EsoClassUtility;
use App\Utility\EsoRoleUtility;

class HelpBotCommand implements BotCommandInterface
{
    public function handle(DiscordRequest $request): DiscordResponse
    {
        return (new DiscordResponse())
            ->addField('List all events', '!events')
            ->addField('Show specific event', '!event [eventID]'.PHP_EOL.'**Example**: `!event 1`')
            ->addField('Attend event', '!attend [eventId] [class] [role]'.PHP_EOL.'**Example**: `!attend 1 dragonknight tank`')
            ->addField('Leave event', '!unattend [eventId]'.PHP_EOL.'**Example**: `!unattend 1`')
            ->addField('See your character presets', '`!characters`')
            ->addField('Change your preferred timezone', '!timezone [timezone]')
            ->addField('List of valid timezones', '[Click here](https://www.php.net/manual/en/timezones.php)')
            ->addField('Usable classes', implode(', ', EsoClassUtility::toArray()))
            ->addField('Usable roles', implode(', ', EsoRoleUtility::toArray()))
            ->setContent($request->getUser()->getDiscordMention());
    }
}
