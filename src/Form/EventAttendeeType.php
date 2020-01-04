<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Entity\ArmorSet;
use App\Entity\CharacterPreset;
use App\Entity\GuildMembership;
use App\Utility\EsoClassUtility;
use App\Utility\EsoRoleUtility;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class EventAttendeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['addOther'] && 0 < $options['user']->getCharacterPresets()->count()) {
            $builder->add(
                'preset',
                EntityType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'class' => CharacterPreset::class,
                    'label' => 'Character preset',
                    'query_builder' => static function (EntityRepository $er) use ($options) {
                        return $er->createQueryBuilder('p')
                            ->where('p.user = :user')
                            ->setParameter('user', $options['user']->getId())
                            ->orderBy('p.name', 'ASC');
                    },
                ]
            );
        }
        if ($options['addOther']) {
            $builder->add(
                'user',
                EntityType::class,
                [
                    'required' => true,
                    'class' => \App\Entity\User::class,
                    'label' => 'User',
                    'query_builder' => static function (EntityRepository $er) use ($options) {
                        return $er->createQueryBuilder('u')
                            ->innerJoin(GuildMembership::class, 'g')
                            ->where('g.guild = :guild')
                            ->setParameter('guild', $options['event']->getGuild()->getId())
                            ->orderBy('u.name', 'ASC')
                            ->orderBy('u.discordDiscriminator', 'ASC');
                    },
                ]
            );
        }
        $builder
            ->add(
                'class',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => array_flip(EsoClassUtility::toArray()),
                ]
            )
            ->add(
                'role',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => array_flip(EsoRoleUtility::toArray()),
                ]
            )
            ->add(
                'sets',
                Select2EntityType::class,
                [
                        'class' => ArmorSet::class,
                        'multiple' => true,
                        'remote_route' => 'api_formfield_armor_sets',
                        'primary_key' => 'id',
                        'text_property' => 'name',
                        'minimum_input_length' => 1,
                        'page_limit' => 10,
                        'allow_clear' => true,
                        'delay' => 250,
                        'cache' => true,
                        'cache_timeout' => 60000, // if 'cache' is true
                        'language' => 'en',
                        'placeholder' => 'Select armor sets',
                    ]
            )
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary btn-block'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\EventAttendee::class,
            'csrf_protection' => 'test' !== getenv('APP_ENV'),
            'user' => null,
            'event' => null,
            'addOther' => false,
        ]);
    }
}
