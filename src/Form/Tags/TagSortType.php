<?php

namespace App\Form\Tags;

use App\Entity\Articles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagSortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('article', EntityType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'всех публикаций',
                'class' => Articles::class,
                'choice_label' => 'title',
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
