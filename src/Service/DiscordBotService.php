<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Service;

use App\Entity\DiscordChannel;
use App\Exception\UnexpectedDiscordApiResponseException;
use GuzzleHttp\Client;
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
     * @param string $botToken
     */
    public function __construct(string $botToken)
    {
        $this->botToken = $botToken;
    }

    /**
     * @param string $channelId
     * @param AbstractDiscordMessage $content
     * @throws UnexpectedDiscordApiResponseException
     */
    public function sendMessage(string $channelId, AbstractDiscordMessage $content): void
    {
        $message = $content->formatForDiscord();
        $message['embed'] = $message['embeds'][0];
        unset($message['embeds']);
        $this->request('https://discordapp.com/api/channels/' . $channelId . '/messages', 'POST', $message);
    }

    /**
     * @param string $serverId
     * @return array
     * @throws UnexpectedDiscordApiResponseException
     */
    public function getChannels(string $serverId): array
    {
        $channels = $this->request('https://discordapp.com/api/guilds/' . $serverId . '/channels');
        $return = [];
        foreach ($channels as $channel) {
            $return[$channel['id']] = $channel;
        }

        return $return;
    }

    /**
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
     * @param string $url
     * @param string $method
     * @param array $payload
     * @return array
     * @throws UnexpectedDiscordApiResponseException
     */
    private function request(string $url, string $method = 'GET', array $payload = []): array
    {
        $client = new Client();
        $sleep = 0;

        while (null !== $sleep) {
            if (0 < $sleep) {
                usleep($sleep * 1000);
            }
            try {
                if ('POST' === $method) {
                    $response = $client->request(
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
                    $response = $client->request(
                        $method,
                        $url,
                        [
                            'headers' => [
                                'Authorization' => 'Bot ' . $this->botToken,
                                'Content-Type' => 'application/json',
                            ],
                        ]
                    );
                }
            } catch (BadResponseException $e) {
                // We don't want to break on 429 too many requests
                if (Response::HTTP_TOO_MANY_REQUESTS !== $e->getResponse()->getStatusCode()) {
                    throw new UnexpectedDiscordApiResponseException('Discord API responded with code ' . $e->getResponse()->getStatusCode(), 1561556557);
                }
            }
            $result = json_decode((string)$response->getBody(), true);
            if (isset($result['retry_after'])) {
                $sleep = $result['retry_after'];
            } else {
                $sleep = null;
            }
        }

        if (200 > $response->getStatusCode() || 400 <= $response->getStatusCode()) {
            throw new UnexpectedDiscordApiResponseException('Discord API responded with code ' . $response->getStatusCode(), 1561556559);
        }

        return $result ?? [];
    }
}