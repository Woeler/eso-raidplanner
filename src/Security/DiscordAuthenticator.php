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
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DiscordAuthenticator extends SocialAuthenticator
{
    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var GuildMembershipRepository
     */
    private $guildMembershipRepository;

    /**
     * @var DiscordClient
     */
    private $discordClient;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router, GuildMembershipRepository $guildMembershipRepository, DiscordClient $discordClient)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->guildMembershipRepository = $guildMembershipRepository;
        $this->discordClient = $discordClient;
    }

    /**
     * Returns a response that directs the user to authenticate.
     *
     * This is called when an anonymous request accesses a resource that
     * requires authentication. The job of this method is to return some
     * response that "helps" the user start into the authentication process.
     *
     * Examples:
     *
     * - For a form login, you might redirect to the login page
     *
     *     return new RedirectResponse('/login');
     *
     * - For an API token authentication system, you return a 401 response
     *
     *     return new Response('Auth header required', 401);
     *
     * @param Request $request The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        return 'auth_discord_return' === $request->attributes->get('_route');
    }

    /**
     * Get the authentication credentials from the request and return them
     * as any type (e.g. an associate array).
     *
     * Whatever value you return here will be passed to getUser() and checkCredentials()
     *
     * For example, for a form login, you might:
     *
     *      return [
     *          'username' => $request->request->get('_username'),
     *          'password' => $request->request->get('_password'),
     *      ];
     *
     * Or for an API token that's on a header, you might use:
     *
     *      return ['api_key' => $request->headers->get('X-API-TOKEN')];
     *
     * @return mixed Any non-null value
     *
     * @throws \UnexpectedValueException If null is returned
     */
    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getDiscordClient());
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed $credentials
     *
     * @param UserProviderInterface $userProvider
     * @return UserInterface|null
     * @throws \Exception
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var AccessToken $credentials */
        $discordUser = $this->getDiscordClient()
            ->fetchUserFromToken($credentials);

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['discordId' => $discordUser->getId()]);
        if ($user) {
            $user->setDiscordToken($credentials->getToken())
                ->setDiscordTokenExpirationDate(
                    new \DateTime(
                        '@'.($credentials->getExpires() ?? 3600),
                        new \DateTimeZone('UTC')
                    )
                )
                ->setDiscordRefreshToken($credentials->getRefreshToken());
        } else {
            $user = new User();
            $user->setDiscordId($discordUser->getId())
                ->setDiscordTokenExpirationDate(
                    new \DateTime(
                        '@'.($credentials->getExpires() ?? 3600),
                        new \DateTimeZone('UTC')
                    )
                )
                ->setDiscordToken($credentials->getToken())
                ->setDiscordRefreshToken($credentials->getRefreshToken());
        }
        $user->setAvatar($discordUser->getAvatarHash() ?? 'unknown')
                ->setDiscordDiscriminator($discordUser->getDiscriminator())
                ->setUsername($discordUser->getUsername());
        $this->em->persist($user);
        $this->em->flush();

        $this->updateGuilds($user, $credentials);

        return $user;
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 403 response.
     *
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication executed and was successful!
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the last page they visited.
     *
     * If you return null, the current request will continue, and the user
     * will be authenticated. This makes sense, for example, with an API.
     *
     * @param string $providerKey The provider (i.e. firewall) key
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('home');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;    }
    }

    /**
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return true;
    }

    /**
     * @return \KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface
     */
    private function getDiscordClient()
    {
        return $this->clientRegistry
            // "facebook_main" is the key used in config/packages/knpu_oauth2_client.yaml
            ->getClient('discord');
    }

    private function updateGuilds(User $user, AccessToken $token)
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
        $guilds = json_decode((string)$guilds->getBody(), true, 512, JSON_THROW_ON_ERROR);
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

            $this->em->persist($newGuild);
            $existingGuilds->add($newGuild);
        }

        foreach ($this->guildMembershipRepository->whereNotIn($user, $existingGuilds) as $membership) {
            $this->em->remove($membership);
        }

        $this->em->flush();
    }
}
