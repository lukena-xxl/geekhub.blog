<?php

namespace App\DataFixtures;

use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Statuses;
use App\Entity\Tags;
use App\Entity\Users;
use App\Services\Common\SlugCreator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $slugCreator = new SlugCreator();
        $dateTime = new \DateTime();

        // Категории
        $arr_categories = [];
        for ($i = 1; $i < 31; $i++) {
            $title = 'Категория ' . $i;

            $categories = new Categories();
            $categories->setTitle($title);
            $categories->setSlug($slugCreator->createSlug($title));
            $categories->setDescription('Описание для ' . $title);

            $arr_categories[] = $categories;
            $manager->persist($categories);
        }

        // Теги
        $arr_tags = [];
        for ($i = 1; $i < 51; $i++) {
            $title = 'Тег ' . $i;

            $tags = new Tags();
            $tags->setTitle($title);
            $tags->setSlug($slugCreator->createSlug($title));

            $arr_tags[] = $tags;
            $manager->persist($tags);
        }

        // Статусы пользователей
        $arr_statuses = [];
        for ($i = 1; $i < 6; $i++) {
            $title = 'Статус ' . $i;

            $statuses = new Statuses();
            $statuses->setTitle($title);

            $arr_statuses[] = $statuses;
            $manager->persist($statuses);
        }

        // Пользователи
        $arr_users = [];
        for ($i = 1; $i < 21; $i++) {
            $name = 'User ' . $i;

            $users = new Users();
            $users->setLogin($slugCreator->createSlug($name));
            $users->setName($name);
            $users->setPassword('12345');
            $users->setRegistrationDate($dateTime);
            $users->setStatus($arr_statuses[array_rand($arr_statuses)]);

            $arr_users[] = $users;
            $manager->persist($users);
        }

        // Публикации
        $arr_articles = [];
        for ($i = 1; $i < 101; $i++) {
            $title = 'Публикация ' . $i;

            $articles = new Articles();
            $articles->setSlug($slugCreator->createSlug($title));
            $articles->setTitle($title);
            $articles->setBody('Тело статьи для ' . $title);
            $articles->setImage('test.jpg');
            $articles->setUpdateDate($dateTime);
            $articles->setCreateDate($dateTime);
            $articles->setCategory($arr_categories[array_rand($arr_categories)]);
            $articles->setUser($arr_users[array_rand($arr_users)]);

            for ($t = 0; $t < rand(1, 5); $t++) {
                $articles->addTag($arr_tags[array_rand($arr_tags)]);
            }

            $arr_articles[] = $articles;
            $manager->persist($articles);
        }

        $manager->flush();
    }
}
