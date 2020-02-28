<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller\Api;

use App\Controller\Checks\TalksWithDiscordBotController;
use App\Message\Discord\Bot\AttendCommandMessage;
use App\Message\Discord\Bot\CharactersCommandMessage;
use App\Message\Discord\Bot\EventCommandMessage;
use App\Message\Discord\Bot\EventsCommandMessage;
use App\Message\Discord\Bot\HelpCommandMessage;
use App\Message\Discord\Bot\TimezoneCommandMessage;
use App\Message\Discord\Bot\UnattendCommandMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/discord", name="api_discord_")
 */
class DiscordBotController extends AbstractController implements TalksWithDiscordBotController
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @Route("/bot", name="bot_entry_point", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function entryPoint(Request $request): Response
    {
        $json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        switch ($json['command'] ?? null) {
            case '!events':
                $this->bus->dispatch(new EventsCommandMessage($json['channelId'], $json));
                break;
            case '!event':
                $this->bus->dispatch(new EventCommandMessage($json['channelId'], $json));
                break;
            case '!attend':
                $this->bus->dispatch(new AttendCommandMessage($json['channelId'], $json));
                break;
            case '!unattend':
                $this->bus->dispatch(new UnattendCommandMessage($json['channelId'], $json));
                break;
            case '!help':
                $this->bus->dispatch(new HelpCommandMessage($json['channelId'], $json));
                break;
            case '!timezone':
                $this->bus->dispatch(new TimezoneCommandMessage($json['channelId'], $json));
                break;
            case '!characters':
                $this->bus->dispatch(new CharactersCommandMessage($json['channelId'], $json));
                break;
            default:
                return Response::create('Unknown command', Response::HTTP_BAD_REQUEST);
        }

        return Response::create('ok', Response::HTTP_OK);
    }
}
