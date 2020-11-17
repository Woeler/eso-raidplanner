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
use GuzzleHttp\Exception\ServerException;
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userRepository->findWhereTokenAlmostExpires();

        foreach ($users as $user) {
            if (null === $user->getDiscordRefreshToken()) {
                continue;
            }
            try {
                $newTokens = $this->discordOauthService->refreshOauthToken($user->getDiscordRefreshToken());
            } catch (ClientException | ServerException $e) {
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

        return 0;
    }
}
