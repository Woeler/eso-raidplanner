<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param EventRepository $eventRepository
     * @param UrlGeneratorInterface $router
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home(EventRepository $eventRepository, UrlGeneratorInterface $router)
    {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->render('home.html.twig');
        }

        /** @var User $user */
        $user = $this->getUser();

        if ('UTC' === $user->getTimezone()) {
            $this->addFlash(
                'warning',
                'Your current timezone is set to UTC, please configure your home timezone in <a href="'
                .$router->generate('user_update')
                .'">your user settings</a>.'
            );
        }
        if (0 < $user->getDiscordGuilds()->count() && 0 === $user->getActiveDiscordGuilds()->count()) {
            $this->addFlash(
                'info',
                'You are not in any active guilds, but you do own Discord servers. You can start the setup process for your Discord servers <a href="'
                .$router->generate('user_guilds')
                .'">here</a>.'
            );
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
