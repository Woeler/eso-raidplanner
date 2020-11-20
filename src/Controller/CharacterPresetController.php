<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Entity\CharacterPreset;
use App\Form\CharacterPresetType;
use App\Repository\CharacterPresetRepository;
use App\Security\Voter\CharacterPresetVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/characterpresets", name="user_preset_")
 */
class CharacterPresetController extends AbstractController
{
    private CharacterPresetRepository $characterPresetRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(CharacterPresetRepository $characterPresetRepository, EntityManagerInterface $entityManager)
    {
        $this->characterPresetRepository = $characterPresetRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/list", name="list")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function list(): Response
    {
        return $this->render(
            'user/character_preset/list.html.twig',
            [
                'presets' => $this->getUser()->getCharacterPresets(),
            ]
        );
    }

    /**
     * @Route("/create", name="create")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $preset = new CharacterPreset();
        $form = $this->createForm(CharacterPresetType::class, $preset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $preset->setUser($this->getUser());
            $this->entityManager->persist($preset);
            $this->entityManager->flush();

            $this->addFlash('success', 'Character preset created.');

            return $this->redirectToRoute('user_preset_list');
        }

        return $this->render(
            'user/character_preset/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/update/{presetId}", name="update")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param int $presetId
     * @return Response
     */
    public function update(Request $request, int $presetId): Response
    {
        $preset = $this->characterPresetRepository->find($presetId);
        $this->denyAccessUnlessGranted(CharacterPresetVoter::UPDATE, $preset);
        $form = $this->createForm(CharacterPresetType::class, $preset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($preset);
            $this->entityManager->flush();

            $this->addFlash('success', 'Character preset updated.');

            return $this->redirectToRoute('user_preset_list');
        }

        return $this->render(
            'user/character_preset/form.html.twig',
            [
                'form' => $form->createView(),
                'preset' => $preset,
            ]
        );
    }

    /**
     * @Route("/delete/{presetId}", name="delete")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @param int $presetId
     * @return Response
     */
    public function delete(Request $request, int $presetId): Response
    {
        $preset = $this->characterPresetRepository->find($presetId);
        $this->denyAccessUnlessGranted(CharacterPresetVoter::DELETE, $preset);
        $this->entityManager->remove($preset);
        $this->entityManager->flush();

        return $this->redirectToRoute('user_preset_list');
    }
}
