<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Command;

use App\Entity\User;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Repository\UserRepository;
use App\Service\DiscordBotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;

class PatreonSyncCommand extends Command
{
    protected static $defaultName = 'patreon:sync';

    /**
     * @var DiscordBotService
     */
    private $discordBotService;

    /**
     * @var string
     */
    private $patreonServer;

    /**
     * @var array
     */
    private $patreonRoles;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        DiscordBotService $discordBotService,
        string $patreonServer,
        array $patreonRoles,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->discordBotService = $discordBotService;
        $this->patreonServer = $patreonServer;
        $this->patreonRoles = $patreonRoles;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store = new SemaphoreStore();
        $factory = new LockFactory($store);
        $lock = $factory->createLock(self::$defaultName, 600);
        $patronIds = ['dummyValue'];

        if ($lock->acquire()) {
            try {
                $members = $this->discordBotService->getMembers($this->patreonServer);
            } catch (UnexpectedDiscordApiResponseException $e) {
                $lock->release();

                return;
            }

            foreach ($members as $member) {
                if (0 === count(array_intersect($member['roles'] ?? [], $this->patreonRoles))) {
                    continue;
                }

                $user = $this->userRepository->findOneBy(['discordId' => $member['user']['id']]);

                if (null === $user) {
                    continue;
                }

                if (in_array($this->patreonRoles[User::PATREON_BRONZE], $member['roles'], true)) {
                    $user->setPatreonMembership(User::PATREON_BRONZE);
                }
                if (in_array($this->patreonRoles[User::PATREON_SILVER], $member['roles'], true)) {
                    $user->setPatreonMembership(User::PATREON_SILVER);
                }
                if (in_array($this->patreonRoles[User::PATREON_GOLD], $member['roles'], true)) {
                    $user->setPatreonMembership(User::PATREON_GOLD);
                }
                if (in_array($this->patreonRoles[User::PATREON_RUBY], $member['roles'], true)) {
                    $user->setPatreonMembership(User::PATREON_RUBY);
                }

                $patronIds[] = $user->getDiscordId();
                $this->entityManager->persist($user);
            }

            foreach ($this->userRepository->findWherePatronAndNotIn($patronIds) as $user) {
                $output->writeln('Removing membership for '.$user->getUsername().'#'.$user->getDiscordDiscriminator());
                $user->setPatreonMembership(User::PATREON_NONE);
                $this->entityManager->persist($user);
            }

            $this->entityManager->flush();
            $lock->release();
        } else {
            $output->writeln('Process already running.');
        }
    }
}
