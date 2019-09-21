<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller\Api;

use App\Repository\ArmorSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/formfield", name="api_formfield_")
 */
class FormFieldController extends AbstractController
{
    /**
     * @Route("/sets", name="armor_sets")
     *
     * @param Request $request
     * @param ArmorSetRepository $armorSetRepository
     * @return Response
     */
    public function armorSets(Request $request, ArmorSetRepository $armorSetRepository): Response
    {
        $sets = $armorSetRepository->searchByName($request->get('q'));
        $return = [];

        foreach ($sets as $set) {
            $return[] = ['id' => $set->getId(), 'text' => $set->getName()];
        }

        return $this->json($return);
    }
}
