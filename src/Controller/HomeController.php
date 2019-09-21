<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('home.html.twig');
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
