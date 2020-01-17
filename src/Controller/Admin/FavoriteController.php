<?php

namespace App\Controller\Admin;

use App\Entity\Articles;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavoriteController
 * @package App\Controller
 * @Route("/admin/addtofavorites", name="admin_favorite")
 */
class FavoriteController extends AbstractController
{
    /**
     * @Route("/articles/{id}", name="_articles")
     * @param Articles $article
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     */
    public function addToFavoritesArticles(Articles $article, EntityManagerInterface $entityManager)
    {
        $targetUser = $entityManager->getRepository(Users::class)->findOneBy(['target' => 1]);

        if ($targetUser) {
            if (in_array($article, $targetUser->getFavoriteArticles()->toArray())) {
                $targetUser->removeFavoriteArticle($article);
                $mes = 'Publication removed from favorites!';
            } else {
                $targetUser->addFavoriteArticle($article);
                $mes = 'Publication added to favorites!';
            }

            $entityManager->persist($targetUser);
            $entityManager->flush();

            return $this->redirectWithMessage('admin_article_show_all', 'success', $mes);

        } else {
            return $this->redirectWithMessage('admin_article_show_all', 'danger', 'Current user not found!');
        }
    }

    /**
     * @Route("/users/{id}", name="_users")
     * @param Users $user
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     */
    public function addToFavoritesUsers(Users $user, EntityManagerInterface $entityManager)
    {
        $targetUser = $entityManager->getRepository(Users::class)->findOneBy(['target' => 1]);

        if ($targetUser) {
            if (in_array($user, $targetUser->getFavoriteUsers()->toArray())) {
                $targetUser->removeFavoriteUser($user);
                $mes = 'User removed from favorites!';
            } else {
                $targetUser->addFavoriteUser($user);
                $mes = 'User added to favorites!';
            }

            $entityManager->persist($targetUser);
            $entityManager->flush();

            return $this->redirectWithMessage('admin_user_show_all', 'success', $mes);
        } else {
            return $this->redirectWithMessage('admin_user_show_all', 'danger', 'Current user not found!');
        }
    }

    private function redirectWithMessage($route, $type, $message)
    {
        $this->addFlash($type, $message);
        return $this->redirectToRoute($route);
    }
}
