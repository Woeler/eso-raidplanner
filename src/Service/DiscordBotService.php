<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Service;

use App\Client\DiscordClient;
use App\Entity\DiscordChannel;
use App\Exception\UnexpectedDiscordApiResponseException;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Component\HttpFoundation\Response;
use Woeler\DiscordPhp\Message\AbstractDiscordMessage;
use Woeler\DiscordPhp\Message\DiscordTextMessage;

class DiscordBotService
{
    /**
     * @var string
     */
    private $botToken;

    /**
     * @var DiscordClient
     */
    private $client;

    /**
     * @var string
     */
    private $botId;

    /**
     * @param string $botToken
     * @param string $botId
     * @param DiscordClient $client
     */
    public function __construct(string $botToken, string $botId, DiscordClient $client)
    {
        $this->botToken = $botToken;
        $this->client = $client;
        $this->botId = $botId;
    }

    /**
     * @param string $channelId
     * @param string $message
     * @throws UnexpectedDiscordApiResponseException
     */
    public function sendTextMessage(string $channelId, string $message): void
    {
        $m = new DiscordTextMessage();
        $m->setContent($message);
        $this->sendMessage($channelId, $m);
    }

    /**
     * @param string $channelId
     * @param AbstractDiscordMessage $content
     * @throws UnexpectedDiscordApiResponseException
     */
    public function sendMessage(string $channelId, AbstractDiscordMessage $content): void
    {
        $this->sendMessageWithArray($channelId, $content->formatForDiscord());
    }

    /**
     * @param string $channelId
     * @param array $content
     * @throws UnexpectedDiscordApiResponseException
     */
    public function sendMessageWithArray(string $channelId, array $content): void
    {
        if (isset($content['embeds'])) {
            $content['embed'] = $content['embeds'][0];
            unset($content['embeds']);
        }
        $this->request('channels/' . $channelId . '/messages', 'POST', $content);
    }

    /**
     * @param string $userId
     * @param AbstractDiscordMessage $message
     * @throws UnexpectedDiscordApiResponseException
     */
    public function sendDirectMessage(string $userId, AbstractDiscordMessage $message): void
    {
        $content = $message->formatForDiscord();
        if (isset($content['embeds'])) {
            $content['embed'] = $content['embeds'][0];
            unset($content['embeds']);
        }
        $channelId = $this->openDirectMessageChannel($userId);
        $this->request('channels/' . $channelId . '/messages', 'POST', $content);
    }

    /**
     * @param string $serverId
     * @return array
     * @throws UnexpectedDiscordApiResponseException
     */
    public function getChannels(string $serverId): array
    {
        $channels = $this->request('guilds/' . $serverId . '/channels');
        $return = [];
        foreach ($channels as $channel) {
            $return[$channel['id']] = $channel;
        }

        return $return;
    }

    /**
     * @param string $serverId
     * @return array
     * @throws UnexpectedDiscordApiResponseException
     */
    public function getTextChannels(string $serverId): array
    {
        $channels = $this->getChannels($serverId);

        foreach ($channels as $key => $channel) {
            if (DiscordChannel::CHANNEL_TYPE_TEXT !== $channel['type']) {
                unset($channels[$key]);
            }
        }

        return $channels;
    }

    /**
     * @param string $serverId
     * @param int $limitPerRequest
     * @return array
     * @throws UnexpectedDiscordApiResponseException
     */
    public function getMembers(string $serverId, int $limitPerRequest = 1000): array
    {
        $members = [];
        $lastId = null;

        while (true) {
            if (null === $lastId) {
                $data = $this->request('guilds/' . $serverId . '/members?limit='.$limitPerRequest);
            } else {
                $data = $this->request('guilds/' . $serverId . '/members?limit='.$limitPerRequest.'&after='.$lastId);
            }

            if (empty($data)) {
                break;
            }

            $lastId = $data[array_key_last($data)]['user']['id'];

            $members = array_merge($members, $data);
        }

        return $members;
    }

    /**
     * @param string $serverId
     * @param string $userId
     * @return array
     * @throws UnexpectedDiscordApiResponseException
     */
    public function getMember(string $serverId, string $userId): array
    {
        return $this->request('guilds/'.$serverId.'/members/'.$userId);
    }

    /**
     * @param string $userId
     * @return array
     * @throws UnexpectedDiscordApiResponseException
     */
    public function getUser(string $userId): array
    {
        return $this->request('users/'.$userId);
    }

    /**
     * @param string $serverId
     * @return array
     * @throws UnexpectedDiscordApiResponseException
     */
    public function getBotUser(string $serverId): array
    {
        return $this->request('guilds/'.$serverId.'/members/'.$this->botId);
    }

    /**
     * @param string $serverId
     * @return array
     * @throws UnexpectedDiscordApiResponseException
     */
    public function getServerRoles(string $serverId): array
    {
        return $this->request('guilds/'.$serverId.'/roles');
    }

    /**
     * @param string $serverId
     * @throws UnexpectedDiscordApiResponseException
     */
    public function leaveServer(string $serverId): void
    {
        $this->request('users/@me/guilds/'.$serverId, 'DELETE');
    }

    public function canViewChannel(array $roles, array $userRoles = [], array $channelOverwrites = []): bool
    {
        $everyone = $roles[0];
        unset($roles[0]);
        $userperms = $everyone['permissions'];
        foreach ($roles as $role) {
            if (!in_array($role['id'] ?? null, $userRoles, true)) {
                continue;
            }
            $userperms |= $role['permissions'];
        }
        $allow = 0;
        $deny = 0;
        foreach ($channelOverwrites as $overwrite) {
            if ('role' === $overwrite['type'] && $overwrite['id'] === $everyone['id']) {
                $userperms &= ~$overwrite['deny'];
                $userperms |= $overwrite['allow'];
            }
        }
        foreach ($channelOverwrites as $overwrite) {
            if ('role' === $overwrite['type'] && in_array($overwrite['id'], $userRoles, true)) {
                $allow |= $overwrite['allow'];
                $deny |= $overwrite['deny'];
            }
        }
        $userperms &= ~$deny;
        $userperms |= $allow;
        foreach ($channelOverwrites as $overwrite) {
            if ('member' === $overwrite['type'] && $this->botId === $overwrite['id']) {
                $userperms &= ~$overwrite['deny'];
                $userperms |= $overwrite['allow'];
                break;
            }
        }

        return 0 !== ($userperms & 0x400);
    }

    /**
     * @param string $userId
     * @return string
     * @throws UnexpectedDiscordApiResponseException
     */
    private function openDirectMessageChannel(string $userId): string
    {
        $response = $this->request('users/@me/channels', 'POST', ['recipient_id' => $userId]);

        return $response['id'];
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $payload
     * @return array
     * @throws UnexpectedDiscordApiResponseException
     */
    private function request(string $url, string $method = 'GET', array $payload = []): array
    {
        $sleep = 0;

        while (null !== $sleep) {
            if (0 < $sleep) {
                usleep($sleep * 1000);
            }
            try {
                if ('POST' === $method) {
                    $response = $this->client->request(
                        $method,
                        $url,
                        [
                            'headers' => [
                                'Authorization' => 'Bot ' . $this->botToken,
                                'Content-Type' => 'application/json',
                            ],
                            'body' => json_encode($payload),
                        ]
                    );
                } else {
                    $response = $this->client->request(
                        $method,
                        $url,
                        [
                            'headers' => [
                                'Authorization' => 'Bot ' . $this->botToken,
                            ],
                        ]
                    );
                }
            } catch (BadResponseException $e) {
                // We don't want to break on 429 too many requests
                if (Response::HTTP_TOO_MANY_REQUESTS !== $e->getResponse()->getStatusCode()) {
                    throw new UnexpectedDiscordApiResponseException('Discord API responded with code ' . $e->getResponse()->getStatusCode(), 1561556557);
                }
                $response = $e->getResponse();
            }
            $result = json_decode((string)$response->getBody(), true);
            if (isset($result['retry_after'])) {
                $sleep = $result['retry_after'];
            } else {
                $sleep = null;
            }
        }

        return $result ?? [];
    }
}
