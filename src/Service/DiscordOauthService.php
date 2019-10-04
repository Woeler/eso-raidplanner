<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Service;

use GuzzleHttp\Client;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DiscordOauthService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(Client $client, TokenStorageInterface $tokenStorage)
    {
        $this->client = $client;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public function getMe(): array
    {
        return $this->request('/users/@me');
    }

    /**
     * @return array
     */
    public function getGuilds(): array
    {
        return $this->request('/users/@me/guilds');
    }

    /**
     * @param string $endpoint
     * @return array
     */
    private function request(string $endpoint): array
    {
        $response = $this->client->get(
            'https://discordapp.com/api'.$endpoint,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->tokenStorage->getToken()->getUser()->getDiscordToken(),
                    'Accept' => 'application/json',
                ],
            ]
        );

        return json_decode((string)$response->getBody(), false) ?? [];
    }
}
