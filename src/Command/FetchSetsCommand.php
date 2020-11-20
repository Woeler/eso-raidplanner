<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Command;

use App\Client\EsoHubClient;
use App\Entity\ArmorSet;
use App\Repository\ArmorSetRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchSetsCommand extends Command
{
    protected static $defaultName = 'sets:fetch';

    private ArmorSetRepository $armorSetRepository;
    private EntityManagerInterface $entityManager;
    private EsoHubClient $client;

    public function __construct(ArmorSetRepository $armorSetRepository, EntityManagerInterface $entityManager, EsoHubClient $client)
    {
        parent::__construct();
        $this->armorSetRepository = $armorSetRepository;
        $this->entityManager = $entityManager;
        $this->client = $client;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $response = $this->client->get('armor-sets');
            $sets = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (BadResponseException | \JsonException $e) {
            return 1;
        }

        foreach ($sets as $set) {
            $entity = $this->armorSetRepository->findOneBy(['esoHubId' => $set['id']]);
            if (null === $entity) {
                $entity = $this->armorSetRepository->findOneBy(['name' => $set['name']]);
            }
            if (null === $entity) {
                $entity = new ArmorSet();
            }

            $entity
                ->setName($set['name'])
                ->setSlug($set['slug'])
                ->setEsoHubId($set['id']);

            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();

        return 0;
    }
}
