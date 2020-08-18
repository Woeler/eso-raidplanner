<?php declare(strict_types=1);

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
use App\Repository\DiscordGuildRepository;
use App\Repository\GuildMembershipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private MessageBusInterface $bus;

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

    /**
     * @Route("/nickname", name="bot_nickname", methods={"POST"})
     *
     * @param Request $request
     * @param GuildMembershipRepository $guildMembershipRepository
     * @param DiscordGuildRepository $guildRepository
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function nicknameChange(
        Request $request,
        GuildMembershipRepository $guildMembershipRepository,
        DiscordGuildRepository $guildRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!array_key_exists('userId', $json) || !array_key_exists('guildId', $json) || !array_key_exists('userNick', $json)) {
            return Response::create('Missing parameters', Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['discordId' => $json['userId']]);
        $guild = $guildRepository->findOneBy(['id' => $json['guildId']]);

        if (null === $user || null === $guild) {
            return Response::create('Missing entities', Response::HTTP_BAD_REQUEST);
        }

        $membership = $guildMembershipRepository->findOneBy(['guild' => $guild, 'user' => $user]);

        if (null !== $membership) {
            $membership->setNickname(urldecode($json['userNick']) === $user->getUsername() ? null : urldecode($json['userNick']));
            $entityManager->persist($membership);
            $entityManager->flush();
        }

        return Response::create('ok', Response::HTTP_OK);
    }
}
