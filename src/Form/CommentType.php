<?php

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommentType extends AbstractType
{
    private UrlGeneratorInterface $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextareaType::class, ['required' => true])
            ->add('submit', SubmitType::class, [
                'label' => 'Add Comment',
                'attr' => ['class' => 'btn btn-primary btn-block'],
            ])
            ->setAction(
                $this->router->generate(
                    'guild_event_comment_create',
                    ['guildId' => $options['event']->getGuild()->getId(), 'eventId' => $options['event']->getId()]
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'event' => null,
        ]);
    }
}
