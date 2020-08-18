<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Command;

use App\Exception\UnexpectedDiscordApiResponseException;
use App\Repository\GuildLogRepository;
use App\Service\DiscordBotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;

class LogsProcessCommand extends Command
{
    protected static $defaultName = 'logs:process';

    private GuildLogRepository $guildLogRepository;

    private DiscordBotService $discordBotService;

    private EntityManagerInterface $entityManager;

    public function __construct(
        GuildLogRepository $guildLogRepository,
        DiscordBotService $discordBotService,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->guildLogRepository = $guildLogRepository;
        $this->discordBotService = $discordBotService;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Sends Discord logs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $store = new SemaphoreStore();
        $factory = new LockFactory($store);
        $lock = $factory->createLock((string)self::$defaultName, 600);

        if ($lock->acquire()) {
            $logs = $this->guildLogRepository->findAll();

            foreach ($logs as $log) {
                try {
                    $this->discordBotService->sendMessageWithArray($log->getChannel(), $log->getData());
                } catch (UnexpectedDiscordApiResponseException $e) {
                }
                $this->entityManager->remove($log);
            }

            $this->entityManager->flush();
            $lock->release();
        } else {
            $output->writeln('Process already running.');
        }

        return 0;
    }
}
