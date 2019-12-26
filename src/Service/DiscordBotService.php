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
     * @param string $botToken
     * @param DiscordClient $client
     */
    public function __construct(string $botToken, DiscordClient $client)
    {
        $this->botToken = $botToken;
        $this->client = $client;
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
     * @throws UnexpectedDiscordApiResponseException
     */
    public function leaveServer(string $serverId): void
    {
        $this->request('users/@me/guilds/'.$serverId, 'DELETE');
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
