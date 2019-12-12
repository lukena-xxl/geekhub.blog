<?php

namespace App\Form\Articles;

use App\Repository\CategoriesRepository;
use App\Repository\TagsRepository;
use App\Repository\UsersRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    private $categoriesRepository;
    private $tagsRepository;
    private $usersRepository;

    public function __construct(CategoriesRepository $categoriesRepository, TagsRepository $tagsRepository, UsersRepository $usersRepository)
    {
        $this->categoriesRepository = $categoriesRepository;
        $this->tagsRepository = $tagsRepository;
        $this->usersRepository = $usersRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categories = $this->categoriesRepository->findAll();
        $choicesCategory = [];
        foreach ($categories as $category) {
            $choicesCategory[$category->getTitle()] = $category->getId();
        }

        $tags = $this->tagsRepository->findAll();
        $choicesTag = [];
        foreach ($tags as $tag) {
            $choicesTag[$tag->getTitle()] = $tag->getId();
        }

        $users = $this->usersRepository->findAll();
        $choicesUser = [];
        foreach ($users as $user) {
            $choicesUser[$user->getLogin()] = $user->getId();
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок публикации',
                'attr' => [
                    'placeholder' => 'Введите заголовок',
                ],
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'label' => 'Slug публикации',
                'help' => 'Разрешены символы: [a-z] _ -',
                'attr' => [
                    'placeholder' => 'Введите slug',
                ],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Категория',
                'choices' => $choicesCategory,
            ])
            ->add('tag', ChoiceType::class, [
                'label' => 'Теги',
                'choices' => $choicesTag,
                'multiple' => true,
            ])
            ->add('user', ChoiceType::class, [
                'label' => 'Привязать к пользователю',
                'choices' => $choicesUser,
            ])
            ->add('body', TextareaType::class, [
                'required' => false,
                'label' => 'Публикация',
                'attr' => [
                    'placeholder' => 'Напишите что-нибудь',
                    'rows' => 10,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Добавить публикацию',
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
