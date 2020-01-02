<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Entity\EventAttendee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventAttendeesStatusType extends AbstractType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var EventAttendee $attendee */
        foreach ($options['attendees'] as $attendee) {
            $builder->add(
                'attendee_' . $attendee->getId(),
                CheckboxType::class,
                [
                    'required' => false,
                    'value' => true,
                    'label' => false,
                ]
            );
        }
        if (count($options['attendees']) > 0) {
            $builder->add(
                'confirm',
                SubmitType::class,
                [
                    'label' => 'Confirm',
                    'attr' => ['class' => 'btn btn-success'],
                ]
            )->add(
                'reserve',
                SubmitType::class,
                [
                    'label' => 'Reserve',
                    'attr' => ['class' => 'btn btn-warning'],
                ]
            )->add(
                'reset',
                SubmitType::class,
                [
                    'label' => 'Reset',
                    'attr' => ['class' => 'btn btn-info'],
                ]
            )->add(
                'delete',
                SubmitType::class,
                [
                    'label' => 'Delete',
                    'attr' => ['class' => 'btn btn-danger'],
                ]
            )->setAction(
                $this->router->generate(
                    'guild_event_attendee_status_change',
                    ['guildId' => $options['event']->getGuild()->getId(), 'eventId' => $options['event']->getId()]
                )
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attendees' => [],
            'event' => null,
        ]);
    }
}
