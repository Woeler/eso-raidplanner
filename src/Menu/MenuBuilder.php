<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Menu;

use App\Entity\User;
use App\Repository\DiscordGuildRepository;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuBuilder
{
    private ContainerInterface $container;

    private FactoryInterface $factory;

    private MatcherInterface $matcher;

    private AuthorizationCheckerInterface $authorizationChecker;

    private TokenStorageInterface $tokenStorage;

    private TranslatorInterface $translator;

    private DiscordGuildRepository $discordGuildRepository;

    public function __construct(
        ContainerInterface $container,
        FactoryInterface $factory,
        MatcherInterface $matcher,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        DiscordGuildRepository $discordGuildRepository
    ) {
        $this->container = $container;
        $this->factory = $factory;
        $this->matcher = $matcher;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->discordGuildRepository = $discordGuildRepository;
    }

    public function mainDefault(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild(
            'home',
            [
                'route' => 'home',
                'label' => 'Home',
                'extras' => [
                    'icon' => 'home',
                ],
            ]
        );
        if ($this->authorizationChecker->isGranted('ROLE_USER')) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            if (0 < count($user->getActiveGuildMemberships())) {
                $menu->addChild(
                    'guilds',
                    [
                        'uri' => '#',
                        'label' => 'Guilds',
                        'extras' => [
                            'icon' => 'users',
                        ],
                    ]
                );
                foreach ($user->getActiveGuildMemberships() as $membership) {
                    $menu['guilds']->addChild(
                        'guilds-' . $membership->getGuild()->getId(),
                        [
                            'route' => 'guild_view',
                            'routeParameters' => ['guildId' => $membership->getGuild()->getId()],
                            'label' => $membership->getGuild()->getName(),
                        ]
                    );
                }
            }
        }

        $menu->addChild(
            'faq',
            [
                'route' => 'faq',
                'label' => 'FAQ & Help',
                'extras' => [
                    'icon' => 'info-circle',
                ],
            ]
        );
        $menu['faq']->addChild(
            'help_getting_started',
            [
                'route' => 'help_getting_started',
                'label' => 'Getting Started',
                'extras' => [
                    'icon' => 'arrow-circle-right',
                ],
            ]
        );
        $menu['faq']->addChild(
            'help_discord_bot',
            [
                'route' => 'help_discord_bot',
                'label' => 'Discord Bot',
                'extras' => [
                    'icon' => 'discord',
                    'fab' => true,
                ],
            ]
        );
        $menu['faq']->addChild(
            'help_recurring_events',
            [
                'route' => 'help_recurring_events',
                'label' => 'Recurring Events',
                'extras' => [
                    'icon' => 'calendar',
                ],
            ]
        );

        $menu->addChild(
            'support',
            [
                'uri' => 'https://woeler.tech',
                'label' => 'Support us',
                'extras' => [
                    'icon' => 'patreon',
                    'fab' => true,
                ],
            ]
        );

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $menu->addChild(
                'admin',
                [
                    'uri' => '/admin',
                    'label' => 'Admin',
                    'extras' => [
                        'icon' => 'cog',
                    ],
                ]
            );
        }

        return $menu;
    }

    public function mainProfile(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $menu->addChild(
                'username',
                [
                    'label' => $user->getUsername(),
                    'uri' => '#',
                    'extras' => [
                        'image' => $user->getFullAvatarUrl(),
                    ],
                ]
            );
            $menu['username']->addChild(
                'user_update',
                [
                    'route' => 'user_update',
                    'label' => 'Settings',
                    'extras' => [
                        'icon' => 'cog',
                    ],
                ]
            );
            $menu['username']->addChild(
                'character_presets',
                [
                    'route' => 'user_preset_list',
                    'label' => 'Your Characters',
                    'extras' => [
                        'icon' => 'chess',
                    ],
                ]
            );
            $menu['username']->addChild(
                'discord_guilds',
                [
                    'route' => 'user_guilds',
                    'label' => 'Your Discord Servers',
                    'extras' => [
                        'icon' => 'discord',
                        'fab' => true,
                    ],
                ]
            );
            $menu['username']->addChild(
                'logout',
                [
                    'route' => 'logout',
                    'label' => 'Logout',
                    'extras' => [
                        'icon' => 'lock',
                    ],
                ]
            );
        } else {
            $menu->addChild(
                'login',
                [
                    'route' => 'auth_discord_login',
                    'label' => 'Login with Discord',
                    'extras' => [
                        'icon' => 'discord',
                        'fab' => true,
                    ],
                ]
            )->setLinkAttribute('class', 'btn btn-primary discord-bg');
        }

        return $menu;
    }

    public function mainFooter(array $options): ItemInterface
    {
        return $this->factory->createItem('root');
    }
}
