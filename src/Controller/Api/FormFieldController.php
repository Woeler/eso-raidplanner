<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller\Api;

use App\Repository\ArmorSetRepository;
use App\Utility\TimezoneUtility;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @IsGranted("ROLE_USER")
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

    /**
     * @Route("/timezones", name="timezones")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * @return Response
     */
    public function timeZones(Request $request): Response
    {
        $query = $request->get('q');
        $zones = array_filter(TimezoneUtility::timeZones(), static function ($item) use ($query) {
            return false !== stripos($item, $query);
        });

        $return = [];

        foreach ($zones as $key => $value) {
            $return[] = ['id' => $key, 'text' => $value];
        }

        return $this->json($return);
    }
}
