<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Tags;
use App\Entity\Users;
use App\Form\Articles\ArticleAddType;
use App\Form\Articles\ArticleSortType;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller
 * @Route("/article", name="article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/show", name="_show_all")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function showAllArticles(Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(ArticleSortType::class, null, [
            'action' => $this->generateUrl('article_show_all'),
            'method' => 'get',
        ]);

        $form->handleRequest($request);
        $arguments = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            if (!empty($formData['user'])) {
                $arguments['user'] = $formData['user'];
            }

            if (!empty($formData['category'])) {
                $arguments['category'] = $formData['category'];
            }

            if (!empty($formData['tag'])) {
                $arguments['tag'] = $formData['tag'];
            }

            if (!empty($formData['from'])) {
                $arguments['create_from'] = $formData['from'];
            }

            if (!empty($formData['to'])) {
                $arguments['create_to'] = $formData['to']->setTime(23, 59, 59);
            }
        }

        $articleRepository = $entityManager->getRepository(Articles::class);

        if (count($arguments) > 0) {
            $articles = $articleRepository->findBySort($arguments);
        } else {
            $articles = $articleRepository->findAll();
        }

        $targetUser = $entityManager->getRepository(Users::class)->findOneBy(['target' => 1]);

        return $this->render('articles/show_all.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
            'target_user' => $targetUser,
            'form_sort' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function showArticle($id)
    {
        return $this->render('articles/show.html.twig', [
            'controller_name' => 'ArticleController',
            'article' => $this->findArticle($id),
        ]);
    }

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
            'action' => $this->generateUrl('article_add'),
            'method' => 'post',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $datetime = new \DateTime();

            $article = new Articles();
            $article->setTitle($formData['title']);
            $article->setBody($formData['body']);

            //$article->setCreateDate($datetime);
            $article->setCreateDate($formData['create_date']);

            $article->setUpdateDate($datetime);

            if(!empty($formData['go_on_public'])) {
                $article->setGoOnPublic($formData['go_on_public']);
            }

            !empty($formData['is_visible']) ? $i = 1 : $i = 0;
            $article->setIsVisible($i);

            if (!empty($formData['slug'])) {
                $article->setSlug($formData['slug']);
            }

            $category = $entityManager->getRepository(Categories::class)->find($formData['category']);
            if ($category) {
                $article->setCategory($category);
            }

            $user = $entityManager->getRepository(Users::class)->find($formData['user']);
            if ($user) {
                $article->setUser($user);
            }

            if (count($formData['tag']) > 0) {
                $repositoryTag = $entityManager->getRepository(Tags::class);
                foreach ($formData['tag'] as $tag_id) {
                    $tag = $repositoryTag->find($tag_id);
                    if ($tag) {
                        $article->addTag($tag);
                    }
                }
            }

            $entityManager->persist($article);
            $entityManager->flush();

            $message = "Добавлена новая публикация \"" . $article->getTitle() . "\" в категорию \"" . $article->getCategory()->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('article_show_all');
        }

        return $this->render('articles/add.html.twig', [
            'controller_name' => 'ArticleController',
            'form_add' => $form->createView(),
            'title' => 'Добавление публикации',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param $id
     * @return Response
     * @throws Exception
     */
    public function editArticle(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $article = $this->findArticle($id);

        $form = $this->createForm(ArticleAddType::class, $article, [
            'action' => $this->generateUrl('article_edit', ['id' => $article->getId()]),
            'method' => 'post',
        ])->remove('create_date');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $article->setUpdateDate(new \DateTime());

            $entityManager->persist($formData);
            $entityManager->flush();

            $message = "Публикация была успешно изменена!";
            $this->addFlash('success', $message);
        }

        return $this->render('articles/add.html.twig', [
            'controller_name' => 'ArticleController',
            'form_add' => $form->createView(),
            'title' => 'Редактирование публикации "' . $article->getTitle() . '"',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param UpdateManager $updateManager
     * @param LoggerInterface $logger
     * @param $id
     * @return Response
     */
    public function deleteArticle(UpdateManager $updateManager, LoggerInterface $logger, $id)
    {
        $article = $this->findArticle($id);

        $assignedTags = $article->getTag();
        if ($assignedTags) {
            foreach ($assignedTags as $assignedTag) {
                $article->removeTag($assignedTag);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        $message = "Публикация \"" . $article->getTitle() . "\" была удалена";
        $logger->info($message);
        $updateManager->notifyOfUpdate($message);
        $this->addFlash('success', $message);

        return $this->redirectToRoute('article_show_all');
    }

    /**
     * @param $id
     * @return Articles
     */
    private function findArticle($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository(Articles::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException(
                'Публикация с идентификатором "' . $id . '" не найдена!'
            );
        } else {
            return $article;
        }
    }
}
