<?php

namespace App\Controller\Fronted;

use App\Entity\Articles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller\Fronted
 * @Route("/articles", name="article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("", name="_show_all")
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function showAllArticles(EntityManagerInterface $entityManager)
    {
        $articles = $entityManager->getRepository(Articles::class)->findAll();

        return $this->render('fronted/articles/show_all.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/{id}", name="_show", requirements={"id"="\d+"})
     * @param Articles $article
     * @return Response
     */
    public function showArticle(Articles $article)
    {
        return $this->render('fronted/articles/show.html.twig', [
            'controller_name' => 'ArticleController',
            'article' => $article,
        ]);
    }
}
