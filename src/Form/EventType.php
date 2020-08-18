<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Entity\DiscordChannel;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('start', DateTimeType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                ],
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'view_timezone' => $options['timezone'],
            ])
            ->add('end', DateTimeType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                ],
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'view_timezone' => $options['timezone'],
                'label' => 'Event end time (not required)',
                'required' => false,
            ])
            ->add(
                'reminderRerouteChannel',
                EntityType::class,
                [
                    'class' => DiscordChannel::class,
                    'empty_data' => '',
                    'label' => 'Re-route reminders to the following channel for this event',
                    'placeholder' => 'Use default channels',
                    'help' => 'If empty, guild default is used.',
                    'required' => false,
                    'query_builder' => static function (EntityRepository $er) use ($options) {
                        return $er->createQueryBuilder('u')
                            ->where('u.guild = :guild')
                            ->setParameter('guild', $options['guild']->getId())
                            ->orderBy('u.name', 'ASC');
                    },
                ]
            )
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary pull-right'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Event::class,
            'csrf_protection' => 'test' !== getenv('APP_ENV'),
            'timezone' => 'UTC',
            'clock' => 24,
            'guild' => null,
        ]);
    }
}
