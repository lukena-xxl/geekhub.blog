<?php

namespace App\Form\Tags;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Нзвание тега',
                'attr' => [
                    'placeholder' => 'Введите название',
                ],
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'label' => 'Slug тега',
                'help' => 'Разрешены символы: [a-z] _ -',
                'attr' => [
                    'placeholder' => 'Введите slug',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Добавить тег',
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
