<?php

namespace App\Form\Articles;

use App\Entity\Categories;
use App\Entity\Tags;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleSortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'всех пользователей',
                'class' => Users::class,
                'choice_label' => 'login',
            ])
            ->add('category', EntityType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'всех категориях',
                'class' => Categories::class,
                'choice_label' => 'title',
            ])
            ->add('tag', EntityType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'всеми тегами',
                'class' => Tags::class,
                'choice_label' => 'title',
            ])
            ->add('from', DateType::class, [
                'required' => false,
                'label' => false,
                'widget' => 'single_text',
            ])
            ->add('to', DateType::class, [
                'required' => false,
                'label' => false,
                'widget' => 'single_text',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Показать',
                'attr' => [
                    'class' => 'btn-secondary btn-sm',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
