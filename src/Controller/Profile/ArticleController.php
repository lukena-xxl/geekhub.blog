<?php

namespace App\Controller\Profile;

use App\Entity\Articles;
use App\Form\Articles\ArticleAddType;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ArticleController
 * @package App\Controller\Profile
 * @Route("/profile/article", name="profile_article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/add", name="_add")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param UpdateManager $updateManager
     * @return Response
     * @throws Exception
     */
    public function addArticle(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, UpdateManager $updateManager)
    {
        $form = $this->createForm(ArticleAddType::class, null, [
            'action' => $this->generateUrl('profile_article_add'),
            'method' => 'post',
            'attr' => [
                'id' => 'article_form',
                'data-article' => '',
            ],
        ])->remove('image')->remove('user');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $datetime = new \DateTime();

            $article = $form->getData();

            $article->setCreateDate($datetime);
            $article->setUpdateDate($datetime);

            $article->setUser($this->getUser());

            $entityManager->persist($article);
            $entityManager->flush();

            $message = "Added new publication \"" . $article->getTitle() . "\" in the category \"" . $article->getCategory()->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);

            $this->addFlash('success', $message);

            return $this->redirectToRoute('profile_main');
        }

        return $this->render('profile/article/add.html.twig', [
            'controller_name' => 'ArticleController',
            'form_add' => $form->createView(),
            'title' => 'Adding a publication',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Articles $article
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws Exception
     */
    public function editArticle(Articles $article, Request $request, EntityManagerInterface $entityManager)
    {
        if ($article->getUser()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException('You do not have access!');
        }

        $form = $this->createForm(ArticleAddType::class, $article, [
            'action' => $this->generateUrl('profile_article_edit', ['id' => $article->getId()]),
            'method' => 'post',
            'attr' => [
                'id' => 'article_form',
            ],
        ])->remove('image')->remove('user');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUpdateDate(new \DateTime());

            $entityManager->persist($article);
            $entityManager->flush();

            $message = "Publication \"" . $article->getTitle() . "\" has been changed";
            $this->addFlash('success', $message);
        }

        return $this->render('profile/article/add.html.twig', [
            'controller_name' => 'ArticleController',
            'form_add' => $form->createView(),
            'image' => $article->getImage(),
            'title' => 'Editing the publication "' . $article->getTitle() . '"',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param Articles $article
     * @param UpdateManager $updateManager
     * @param LoggerInterface $logger
     * @return Response
     */
    public function deleteArticle(Articles $article, UpdateManager $updateManager, LoggerInterface $logger)
    {
        if ($article->getUser()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException('You do not have access!');
        }

        $tags = $article->getTag();

        if ($tags) {
            foreach ($tags as $tag) {
                $article->removeTag($tag);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        $message = "Publication \"" . $article->getTitle() . "\" has been deleted";
        $logger->info($message);
        $updateManager->notifyOfUpdate($message);
        $this->addFlash('success', $message);

        return $this->redirectToRoute('profile_main');
    }
}
