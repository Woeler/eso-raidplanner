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
use App\Utility\TimezoneUtility;
use Doctrine\ORM\EntityManagerInterface;

class TimezoneBotCommand implements BotCommandInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handle(DiscordRequest $request): DiscordResponse
    {
        $response = new DiscordResponse();
        $timezone = trim($request->getArgs());
        $user = $request->getUser();

        if (array_key_exists($timezone, TimezoneUtility::timeZones())) {
            $user->setTimezone($timezone);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $response->setContent($user->getDiscordMention().' Your timezone has been set to '.$timezone.'.');
        } elseif (empty($timezone)) {
            $response->setContent(
                $user->getDiscordMention().' Please specify a timezone in your command. Here is an example `!timezone Europe/Berlin`, and here is a list of supported timezones https://www.php.net/manual/en/timezones.php'
            );
        } else {
            $response->setContent(
                $user->getDiscordMention() . ' I do not know the timezone ' . $timezone . '. Please make sure to check out this official timezone list. Also be aware that the timezone you give me is case sensitive. https://www.php.net/manual/en/timezones.php'
                . PHP_EOL . 'Here is an example `!timezone Europe/Berlin`'
            );
        }

        return $response->setOnlyText(true);
    }
}
