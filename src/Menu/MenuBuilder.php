<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Menu;

use App\Entity\GuildMembership;
use App\Entity\User;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuBuilder
{
    private FactoryInterface $factory;
    private AuthorizationCheckerInterface $authorizationChecker;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        FactoryInterface $factory,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->factory = $factory;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
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
                /** @var GuildMembership $membership */
                $i = 0;
                foreach ($user->getActiveGuildMemberships() as $membership) {
                    $menu['guilds']->addChild(
                        'guilds-' . $i,
                        [
                            'route' => 'guild_view',
                            'routeParameters' => ['guildId' => $membership->getGuild()->getId()],
                            'label' => $membership->getGuild()->getName(),
                        ]
                    );
                    $i++;
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
                'uri' => 'https://woeler.dev',
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
