<?php

namespace App\Form\Categories;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Нзвание категории',
                'attr' => [
                    'placeholder' => 'Введите название',
                ],
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'label' => 'Slug категории',
                'help' => 'Разрешены символы: [a-z] _ -',
                'attr' => [
                    'placeholder' => 'Введите slug',
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Описание категории',
                'attr' => [
                    'placeholder' => 'Введите описание',
                    'rows' => 10,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Добавить категорию',
                'attr' => [
                    'class' => 'btn-success',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
