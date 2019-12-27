<?php

namespace App\Form\Articles;

use App\Entity\Categories;
use App\Entity\Tags;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateTime = new \DateTime();

        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок',
                'attr' => [
                    'placeholder' => 'Введите заголовок',
                ],
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'label' => 'Slug',
                'help' => 'Разрешены символы: [a-z] _ -',
                'attr' => [
                    'placeholder' => 'Введите slug',
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Категория',
                'class' => Categories::class,
                'choice_label' => 'title',
            ])
            ->add('tag', EntityType::class, [
                'required' => false,
                'label' => 'Теги',
                'class' => Tags::class,
                'choice_label' => 'title',
                'multiple' => true,
            ])
            ->add('user', EntityType::class, [
                'label' => 'Привязать к пользователю',
                'class' => Users::class,
                'choice_label' => 'login',
            ])
            ->add('body', TextareaType::class, [
                'required' => false,
                'label' => 'Публикация',
                'attr' => [
                    'placeholder' => 'Напишите что-нибудь',
                    'rows' => 10,
                ],
            ])
            ->add('go_on_public', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Запланировать дату начала показа',
                'help' => 'Выберите дату начала показа публикации или оставьте поле пустым для немедленной публикации',
                'attr' => [
                    'min' => $dateTime->format('Y-m-d\TH:i'),
                ],
            ])
            ->add('is_visible', CheckboxType::class, [
                'required' => false,
                'value' => 1,
                'label' => 'Включена/Отключена',
                'label_attr' => [
                    'class' => 'font-weight-normal text-dark',
                ],
            ])
            ->add('create_date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Дата создания публикации',
                'help' => 'Эта дата будет записана, как дата создания публикации',
                'attr' => [
                    'value' => $dateTime->format('Y-m-d\TH:i:s'),
                    'readonly' => true,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Сохранить',
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
