<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Command;

use App\Entity\ArmorSet;
use App\Repository\ArmorSetRepository;
use Doctrine\ORM\EntityManagerInterface;
use PathfinderMediaGroup\ApiLibrary\Api\SetApi;
use PathfinderMediaGroup\ApiLibrary\Auth\TokenAuth;
use PathfinderMediaGroup\ApiLibrary\Exception\FailedPmgRequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchSetsCommand extends Command
{
    protected static $defaultName = 'sets:fetch';

    private ArmorSetRepository $armorSetRepository;

    private string $pmgToken;

    private EntityManagerInterface $entityManager;

    public function __construct(ArmorSetRepository $armorSetRepository, EntityManagerInterface $entityManager, string $pmgToken)
    {
        parent::__construct();
        $this->armorSetRepository = $armorSetRepository;
        $this->pmgToken = $pmgToken;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Add a short description for your command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $token = new TokenAuth($this->pmgToken);
        $api = new SetApi($token);

        try {
            $sets = $api->getAll();
        } catch (FailedPmgRequestException $e) {
            return 1;
        }

        foreach ($sets as $set) {
            $entity = $this->armorSetRepository->find($set['id']);
            if (empty($set['id'])) {
                continue;
            }
            if (null === $entity) {
                $entity = new ArmorSet();
                $entity->setId($set['id']);
            }

            $entity
                ->setName($set['name'])
                ->setSlug($set['slug']);

            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();

        return 0;
    }
}
