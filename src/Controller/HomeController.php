<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param EventRepository $eventRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home(EventRepository $eventRepository)
    {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->render('home.html.twig');
        }

        if ('UTC' === $this->getUser()->getTimezone()) {
            $this->addFlash('warning', 'Your current timezone is set to UTC, please configure your home timezone in <a href="/user/update">your user settings</a>.');
        }

        return $this->render(
            'home_logged_in.html.twig',
            [
                'events' => $eventRepository->findFutureEventsForUser($this->getUser()),
            ]
        );
    }

    /**
     * @Route("/privacy", name="privacy_policy")
     */
    public function privacyPolicy()
    {
        return $this->render('static_pages/privacy_policy_html.twig');
    }

    /**
     * @Route("/termsofuse", name="terms_of_use")
     */
    public function termsOfUse()
    {
        return $this->render('static_pages/terms_of_use.html.twig');
    }
}
