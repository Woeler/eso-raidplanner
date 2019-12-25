<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Service;

use App\Client\DiscordClient;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DiscordOauthService
{
    /**
     * @var DiscordClient
     */
    private $client;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    public function __construct(
        DiscordClient $client,
        TokenStorageInterface $tokenStorage,
        string $clientId,
        string $clientSecret
    ) {
        $this->client = $client;
        $this->tokenStorage = $tokenStorage;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return array
     */
    public function getMe(): array
    {
        return $this->getRequest('/users/@me');
    }

    /**
     * @return array
     */
    public function getGuilds(): array
    {
        return $this->getRequest('/users/@me/guilds');
    }

    public function refreshOauthToken(string $refreshToken): array
    {
        return $this->postRequest(
            '/oauth2/token',
            [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]
        );
    }

    /**
     * @param string $endpoint
     * @return array
     */
    private function getRequest(string $endpoint): array
    {
        $response = $this->client->get(
            $endpoint,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->tokenStorage->getToken()->getUser()->getDiscordToken(),
                    'Accept' => 'application/json',
                ],
            ]
        );

        return json_decode((string)$response->getBody(), false) ?? [];
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    private function postRequest(string $endpoint, array $data = []): array
    {
        $response = $this->client->post(
            $endpoint,
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $data,
            ]
        );

        return json_decode((string)$response->getBody(), true) ?? [];
    }
}
