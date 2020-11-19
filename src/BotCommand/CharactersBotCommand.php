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

class CharactersBotCommand implements BotCommandInterface
{
    public function handle(DiscordRequest $request): DiscordResponse
    {
        $response = new DiscordResponse();
        $presets = $request->getUser()->getCharacterPresets();

        if (0 === count($presets)) {
            $response
                ->setContent($request->getUser()->getDiscordMention().' You currently have no character presets.')
                ->setOnlyText(true);
        } else {
            foreach ($presets as $preset) {
                $response->addField(
                    $preset->getName(),
                    $preset->getClassName() . ' ' . $preset->getRoleName() . ' ' . (0 < $preset->getSets()->count() ? 'with sets ' . implode(', ', $preset->getSets()->toArray()) : ''),
                    false
                );
            }
        }

        return $response;
    }
}
