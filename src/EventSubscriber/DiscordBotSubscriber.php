<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\EventSubscriber;

use App\Controller\Checks\TalksWithDiscordBotController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class DiscordBotSubscriber implements EventSubscriberInterface
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function onTalksWithDiscordController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof TalksWithDiscordBotController) {
            $token = str_replace('Bearer ', '', $event->getRequest()->headers->get('Authorization') ?? '');
            if ($this->token !== $token) {
                throw new UnauthorizedHttpException('Invalid token');
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onTalksWithDiscordController',
        ];
    }
}
