<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller\Auth;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/auth/discord", name="auth_discord_")
 */
class DiscordLoginController extends AbstractController
{
    /**
     * @Route("/return", name="return")
     *
     * @param Request $request
     * @return Response
     */
    public function return(Request $request): Response
    {
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/login", name="login")
     *
     * @param Request $request
     * @param ClientRegistry $clientRegistry
     * @return Response
     */
    public function login(Request $request, ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('discord')
            ->redirect(['identify', 'guilds'], ['prompt' => 'none']);
    }
}
