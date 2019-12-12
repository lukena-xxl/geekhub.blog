<?php

namespace App\DataFixtures;

use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Statuses;
use App\Entity\Tags;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $factory = new Factory();
        $faker = $factory->create('en_EN');

        // Категории
        $arr_categories = [];
        for ($i = 1; $i < 31; $i++) {
            $categories = new Categories();
            $categories->setTitle($faker->text(20));
            $categories->setDescription($faker->text(500));
            $categories->setIsVisible(1);

            $arr_categories[] = $categories;
            $manager->persist($categories);
        }

        // Теги
        $arr_tags = [];
        for ($i = 1; $i < 51; $i++) {
            $tags = new Tags();
            $tags->setTitle($faker->word);
            $tags->setIsVisible(1);

            $arr_tags[] = $tags;
            $manager->persist($tags);
        }

        // Статусы пользователей
        $arr = ['admin', 'moderator', 'user'];
        $arr_statuses = [];
        foreach ($arr as $status) {
            $statuses = new Statuses();
            $statuses->setTitle($status);

            $arr_statuses[] = $statuses;
            $manager->persist($statuses);
        }

        // Пользователи
        $arr_users = [];
        for ($i = 1; $i < 21; $i++) {
            $users = new Users();
            $users->setLogin($faker->userName);

            $arr_gender = ['male', 'female'];
            $gender = $arr_gender[array_rand($arr_gender)];
            $users->setGender($gender);

            $users->setName($faker->name($gender));
            $users->setPassword($faker->password(8, 20));
            $users->setRegistrationDate($faker->dateTimeBetween('-17 years', 'now'));
            $users->setBirthDate($faker->dateTimeBetween('-60 years', '-18 years'));

            $users->setStatus($arr_statuses[array_rand($arr_statuses)]);

            $arr_users[] = $users;
            $manager->persist($users);
        }

        // Публикации
        $arr_articles = [];
        for ($i = 1; $i < 101; $i++) {
            $articles = new Articles();
            $articles->setTitle($faker->text(70));
            $articles->setBody($faker->text(700));
            //$articles->setImage();
            $articles->setCreateDate($faker->dateTimeBetween('-3 years', '-6 months'));
            $articles->setUpdateDate($faker->dateTimeBetween('-5 months', 'now'));

            if (($i % 5) == 1) {
                $articles->setGoOnPublic($faker->dateTimeBetween('now', '+5 months'));
            }

            $articles->setIsVisible(1);

            $articles->setCategory($arr_categories[array_rand($arr_categories)]);
            $articles->setUser($arr_users[array_rand($arr_users)]);

            for ($t = 0; $t < rand(1, 5); $t++) {
                $articles->addTag($arr_tags[array_rand($arr_tags)]);
            }

            $arr_articles[] = $articles;
            $manager->persist($articles);
        }

        // Избранные публикации
        for ($i = 1; $i < 51; $i++) {
            $user_article = $arr_users[array_rand($arr_users)];

            for ($t = 0; $t < rand(1, 5); $t++) {
                $user_article->addFavoriteArticle($arr_articles[array_rand($arr_articles)]);
            }

            $manager->persist($user_article);
        }

        // Избранные пользователи
        for ($i = 1; $i < 31; $i++) {
            $all_users = $arr_users;
            $key = array_rand($all_users);
            $user_user = $all_users[$key];

            unset($all_users[$key]);

            for ($t = 0; $t < rand(1, 5); $t++) {
                $user_user->addFavoriteUser($all_users[array_rand($all_users)]);
            }

            $manager->persist($user_user);
        }

        $manager->flush();
    }
}
