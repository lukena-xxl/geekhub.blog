<?php

namespace App\Form\Users;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserTargetType extends AbstractType
{
    private $repository;

    public function __construct(UsersRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'required' => false,
                'label' => false,
                'class' => Users::class,
                'choice_label' => 'login',
                'placeholder' => 'choose',
                'data' => $this->repository->findOneBy(['target' => 1])
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Select',
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
