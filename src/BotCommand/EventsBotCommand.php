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
use App\Repository\EventRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventsBotCommand implements BotCommandInterface
{
    private EventRepository $eventRepository;
    private UrlGeneratorInterface $router;

    public function __construct(EventRepository $eventRepository, UrlGeneratorInterface $router)
    {
        $this->eventRepository = $eventRepository;
        $this->router = $router;
    }

    public function handle(DiscordRequest $request): DiscordResponse
    {
        $response = new DiscordResponse();
        $events = $this->eventRepository->findFutureEventsForGuild($request->getGuild());
        $desc = '';
        $i = 0;
        foreach ($events as $event) {
            if (5 === $i) {
                break;
            }
            $eventUrl = $this->router->generate(
                'guild_event_view',
                ['guildId' => $request->getGuild()->getId(), 'eventId' => $event->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $eventName = '['.$event->getName().']('.$eventUrl.')';
            $desc .= $event->getId().': **'.$eventName.'**'.PHP_EOL.$request->getUser()->toUserTimeString($event->getStart()).PHP_EOL.PHP_EOL;
            $i++;
        }

        $response
            ->setTitle('Upcoming events')
            ->setAuthorIcon('https://cdn.discordapp.com/icons/'.$request->getGuild()->getId().'/'.$request->getGuild()->getIcon().'.png')
            ->setAuthorName($request->getGuild()->getName())
            ->addField('Times displayed in your timezone', $request->getUser()->getTimezone())
            ->setDescription($desc)
            ->setContent($request->getUser()->getDiscordMention());

        return $response;
    }
}
