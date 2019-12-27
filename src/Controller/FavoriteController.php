<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavoriteController
 * @package App\Controller
 * @Route("/addtofavorites", name="favorite")
 */
class FavoriteController extends AbstractController
{
    /**
     * @Route("/articles/{id}", name="_articles")
     * @param EntityManagerInterface $entityManager
     * @param $id
     * @return RedirectResponse
     */
    public function addToFavoritesArticles(EntityManagerInterface $entityManager, $id)
    {
        if (!empty($id)) {
            $article = $entityManager->getRepository(Articles::class)->find($id);

            if ($article) {
                $targetUser = $entityManager->getRepository(Users::class)->findOneBy(['target' => 1]);

                if ($targetUser) {
                    if (in_array($article, $targetUser->getFavoriteArticles()->toArray())) {
                        $targetUser->removeFavoriteArticle($article);
                    } else {
                        $targetUser->addFavoriteArticle($article);
                    }

                    $entityManager->persist($targetUser);
                    $entityManager->flush();
                }
            }
        }

        return $this->redirectToRoute('article_show_all');
    }

    /**
     * @Route("/users/{id}", name="_users")
     * @param EntityManagerInterface $entityManager
     * @param $id
     * @return RedirectResponse
     */
    public function addToFavoritesUsers(EntityManagerInterface $entityManager, $id)
    {
        if (!empty($id)) {
            $userRepository = $entityManager->getRepository(Users::class);

            $user = $userRepository->find($id);

            if ($user) {
                $targetUser = $userRepository->findOneBy(['target' => 1]);

                if ($targetUser) {
                    if (in_array($user, $targetUser->getFavoriteUsers()->toArray())) {
                        $targetUser->removeFavoriteUser($user);
                    } else {
                        $targetUser->addFavoriteUser($user);
                    }

                    $entityManager->persist($targetUser);
                    $entityManager->flush();
                }
            }
        }

        return $this->redirectToRoute('user_show_all');
    }
}
