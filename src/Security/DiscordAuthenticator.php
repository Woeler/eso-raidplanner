<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Security;

use App\Client\DiscordClient;
use App\Entity\DiscordGuild;
use App\Entity\User;
use App\Repository\GuildMembershipRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DiscordAuthenticator extends SocialAuthenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $em;
    private RouterInterface $router;
    private GuildMembershipRepository $guildMembershipRepository;
    private DiscordClient $discordClient;
    private SessionInterface $session;
    private array $defaultRoles;
    private array $admins;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        RouterInterface $router,
        GuildMembershipRepository $guildMembershipRepository,
        DiscordClient $discordClient,
        SessionInterface $session,
        array $defaultRoles,
        array $admins
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->guildMembershipRepository = $guildMembershipRepository;
        $this->discordClient = $discordClient;
        $this->session = $session;
        $this->defaultRoles = $defaultRoles;
        $this->admins = $admins;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $userAgent = $request->headers->get('User-Agent');
        if (str_contains($userAgent, 'Discordbot')) {
            return new Response(Response::HTTP_PRECONDITION_FAILED);
        }

        $routeName = $request->attributes->get('_route');
        $routeParameters = $request->attributes->get('_route_params');

        $routeUrl = $this->router->generate(
            $routeName,
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->session->set('redirect_url', $routeUrl);
        
        return new RedirectResponse(
            $this->router->generate('auth_discord_login'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    public function supports(Request $request): bool
    {
        return 'auth_discord_return' === $request->attributes->get('_route');
    }

    public function getCredentials(Request $request): AccessToken
    {
        return $this->fetchAccessToken($this->getDiscordClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider): User
    {
        /** @var AccessToken $credentials */
        $discordUser = $this->getDiscordClient()
            ->fetchUserFromToken($credentials);

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['discordId' => $discordUser->getId()]);
        if (null === $user) {
            $user = (new User())
                ->setDiscordId($discordUser->getId());
        }
        $user->setAvatar($discordUser->getAvatarHash() ?? 'unknown')
            ->setDiscordDiscriminator($discordUser->getDiscriminator())
            ->setUsername($discordUser->getUsername())
            ->setDiscordTokenExpirationDate(
                new \DateTime(
                    '@'.($credentials->getExpires() ?? 3600),
                    new \DateTimeZone('UTC')
                )
            )
            ->setDiscordToken($credentials->getToken())
            ->setDiscordRefreshToken($credentials->getRefreshToken())
            ->setRoles($this->defaultRoles);

        if (in_array($user->getDiscordId(), $this->admins, true)) {
            $user->setRoles(array_merge($user->getRoles(), ['ROLE_ADMIN']));
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->updateGuilds($user, $credentials);

        return $user;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): RedirectResponse
    {
        $defaultTargetUrl = $this->router->generate('home');
        
        $targetUrl = $this->session->get('redirect_url', $defaultTargetUrl);

        return new RedirectResponse($targetUrl);
    }

    private function getDiscordClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('discord');
    }

    private function updateGuilds(User $user, AccessToken $token): void
    {
        $guilds = $this->discordClient->get(
            'users/@me/guilds',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                    'Accept' => 'application/json',
                ],
            ]
        );
        $guilds = json_decode((string)$guilds->getBody(), false, 512, JSON_THROW_ON_ERROR);
        $existingGuilds = new ArrayCollection();

        foreach ($guilds as $guild) {
            $newGuild = $this->em->getRepository(DiscordGuild::class)
                    ->findOneBy(['id' => $guild->id]);
            if (null === $newGuild) {
                $newGuild = new DiscordGuild();
            }
            $newGuild
                ->setName($guild->name)
                ->setDiscordId($guild->id)
                ->setIcon($guild->icon)
                ->addMember($user);
            if ($guild->owner) {
                $newGuild->setOwner($user)
                    ->makeAdmin($user);
            } else {
                $newGuild->addMember($user);
            }
            $newGuild->generateIcalId();

            $this->em->persist($newGuild);
            $existingGuilds->add($newGuild);
        }

        foreach ($this->guildMembershipRepository->whereNotIn($user, $existingGuilds) as $membership) {
            $this->em->remove($membership);
        }

        $this->em->flush();
    }
}
