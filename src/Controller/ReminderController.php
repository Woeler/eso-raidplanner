<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Controller;

use App\Entity\Reminder;
use App\Exception\UnexpectedDiscordApiResponseException;
use App\Form\ReminderType;
use App\Repository\DiscordGuildRepository;
use App\Repository\ReminderRepository;
use App\Security\Voter\GuildVoter;
use App\Security\Voter\ReminderVoter;
use App\Service\DiscordBotService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Woeler\DiscordPhp\Message\DiscordEmbedsMessage;

/**
 * @Route("/guild", name="guild_")
 */
class ReminderController extends AbstractController
{
    /**
     * @var DiscordGuildRepository
     */
    private $discordGuildRepository;

    /**
     * @var ReminderRepository
     */
    private $reminderRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        DiscordGuildRepository $discordGuildRepository,
        ReminderRepository $reminderRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->discordGuildRepository = $discordGuildRepository;
        $this->reminderRepository = $reminderRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/{guildId}/reminder/create", name="reminder_create")
     * @IsGranted("ROLE_USER")
     *
     * @param int $guildId
     * @param Request $request
     * @return Response
     */
    public function createReminder(int $guildId, Request $request): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::CREATE_REMINDER, $guild);
        $reminder = new Reminder();
        $form = $this->createForm(ReminderType::class, $reminder, ['guild' => $guild]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reminder->setGuild($guild);
            $this->entityManager->persist($reminder);
            $this->entityManager->flush();
            $this->addFlash('success', 'Reminder '.$reminder->getName().' created.');

            return $this->redirectToRoute('guild_reminder_list', ['guildId' => $guildId]);
        }

        return $this->render(
            'reminder/form.html.twig',
            [
                'form' => $form->createView(),
                'guild' => $guild,
            ]
        );
    }

    /**
     * @Route("/{guildId}/reminder/{reminderId}/update", name="reminder_update")
     * @IsGranted("ROLE_USER")
     *
     * @param int $guildId
     * @param int $reminderId
     * @param Request $request
     * @return Response
     */
    public function updateReminder(int $guildId, int $reminderId, Request $request): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $reminder = $this->reminderRepository->find($reminderId);
        $this->denyAccessUnlessGranted(ReminderVoter::UPDATE, $reminder);

        $form = $this->createForm(ReminderType::class, $reminder, ['guild' => $guild]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($reminder);
            $this->entityManager->flush();
            $this->addFlash('success', 'Reminder '.$reminder->getName().' updated.');

            return $this->redirectToRoute('guild_reminder_list', ['guildId' => $guildId]);
        }

        return $this->render(
            'reminder/form.html.twig',
            [
                'form' => $form->createView(),
                'guild' => $guild,
            ]
        );
    }

    /**
     * @Route("/{guildId}/reminder/{reminderId}/delete", name="reminder_delete")
     * @IsGranted("ROLE_USER")
     *
     * @param string $guildId
     * @param int $reminderId
     * @param Request $request
     * @return Response
     */
    public function deleteReminder(string $guildId, int $reminderId, Request $request): Response
    {
        $reminder = $this->reminderRepository->find($reminderId);
        $this->denyAccessUnlessGranted(ReminderVoter::DELETE, $reminder);

        $this->entityManager->remove($reminder);
        $this->entityManager->flush();
        $this->addFlash('success', 'Reminder was deleted.');

        return $this->redirectToRoute('guild_reminder_list', ['guildId' => $guildId]);
    }

    /**
     * @Route("/{guildId}/reminders", name="reminder_list")
     * @IsGranted("ROLE_USER")
     *
     * @param string $guildId
     * @return Response
     */
    public function reminders(string $guildId): Response
    {
        $guild = $this->discordGuildRepository->findOneBy(['id' => $guildId]);
        $this->denyAccessUnlessGranted(GuildVoter::CREATE_REMINDER, $guild);

        return $this->render(
            'reminder/list.html.twig',
            [
                'guild' => $guild,
            ]
        );
    }

    /**
     * @Route("/{guildId}/reminder/{reminderId}/test", name="reminder_test")
     * @IsGranted("ROLE_USER")
     *
     * @param string $guildId
     * @param int $reminderId
     * @param DiscordBotService $discordBotService
     * @param UrlGeneratorInterface $router
     * @return Response
     */
    public function testReminder(
        string $guildId,
        int $reminderId,
        DiscordBotService $discordBotService,
        UrlGeneratorInterface $router
    ): Response {
        $reminder = $this->reminderRepository->find($reminderId);
        $this->denyAccessUnlessGranted(ReminderVoter::UPDATE, $reminder);
        $message = (new DiscordEmbedsMessage())
            ->setColor(9660137)
            ->setTitle('This is a test message')
            ->addField('Triggered by', $this->getUser()->getDiscordMention())
            ->setAuthorName($reminder->getGuild()->getName())
            ->setAuthorIcon('https://cdn.discordapp.com/icons/' . $reminder->getGuild()->getId() . '/' . $reminder->getGuild()->getIcon() . '.png')
            ->setAuthorUrl($router->generate(
                'guild_view',
                ['guildId' => $reminder->getGuild()->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ))
            ->setFooterIcon('https://esoraidplanner.com/favicon/appicon.jpg')
            ->setFooterText('Reminder: '.$reminder->getName());

        try {
            $discordBotService->sendMessage($reminder->getChannel()->getId(), $message);
            $this->addFlash('success', 'Test message sent.');
        } catch (UnexpectedDiscordApiResponseException $e) {
            $this->addFlash('danger', 'Message could not be sent. Please check if the bot has the correct rights.');
        }

        return $this->redirectToRoute('guild_reminder_list', ['guildId' => $reminder->getGuild()->getId()]);
    }
}
