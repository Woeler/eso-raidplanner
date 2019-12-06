<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\DiscordOauthService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshDiscordTokensCommand extends Command
{
    protected static $defaultName = 'discord:tokens:refresh';

    /**
     * @var DiscordOauthService
     */
    private $discordOauthService;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(DiscordOauthService $discordOauthService, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->discordOauthService = $discordOauthService;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Refreshes Discord tokens that almost expired')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->userRepository->findWhereTokenAlmostExpires();

        foreach ($users as $user) {
            try {
                $newTokens = $this->discordOauthService->refreshOauthToken($user->getDiscordRefreshToken());
            } catch (ClientException $e) {
                continue;
            }
            $user->setDiscordToken($newTokens['access_token'])
                ->setDiscordRefreshToken($newTokens['refresh_token'])
                ->setDiscordTokenExpirationDate(
                    new \DateTime('+'.$newTokens['expires_in'].' seconds', new \DateTimeZone('UTC'))
                );
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
    }
}
