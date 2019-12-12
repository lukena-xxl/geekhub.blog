<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Tags;
use App\Entity\Users;
use App\Form\Articles\ArticleType;
use App\Repository\ArticlesRepository;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @param ArticlesRepository $articlesRepository
     * @return Response
     */
    public function showAllArticles(Request $request, ArticlesRepository $articlesRepository)
    {
        $arguments = [];

        if ($request->query->has('sort')) {
            $sorting = $request->query->get('sort');

            if (!empty($sorting['user'])) {
                $arguments['user'] = $sorting['user'];
            }

            if (!empty($sorting['category'])) {
                $arguments['category'] = $sorting['category'];
            }

            if (!empty($sorting['tag'])) {
                $arguments['tag'] = $sorting['tag'];
            }

            if (!empty($sorting['create_from'])) {
                $arguments['create_from'] = $sorting['create_from'];
            }

            if (!empty($sorting['create_to'])) {
                $arguments['create_to'] = $sorting['create_to'];
            }
        }

        if (count($arguments) > 0) {
            $articles = $articlesRepository->findBySort($arguments);
        } else {
            $articles = $articlesRepository->findAll();
        }

        $entityManager = $this->getDoctrine()->getManager();
        $categories = $entityManager->getRepository(Categories::class)->findAll();
        $users = $entityManager->getRepository(Users::class)->findAll();
        $tags = $entityManager->getRepository(Tags::class)->findAll();

        return $this->render('articles/show_all.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
            'users' => $users,
            'categories' => $categories,
            'tags' => $tags,
            'arguments' => $arguments,
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
            'article' => $this->getArticleData($id),
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
        $form = $this->createForm(ArticleType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $article = new Articles();
            $article->setTitle($formData['title']);
            $article->setBody($formData['body']);
            $article->setCreateDate(new \DateTime());
            $article->setUpdateDate(new \DateTime());

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
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UpdateManager $updateManager
     * @param $id
     */
    public function editArticle(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {

    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param UpdateManager $updateManager
     * @param $id
     * @return Response
     */
    public function deleteArticle(UpdateManager $updateManager, $id)
    {
        $article = $this->getArticleData($id);

        $assignedTags = $article->getTag();
        if ($assignedTags) {
            foreach ($assignedTags as $assignedTag) {
                $article->removeTag($assignedTag);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        $message = "Публикация с идентификатором \"" . $id . "\" была удалена!";
        $updateManager->notifyOfUpdate($message);

        return $this->render('articles/access_delete.html.twig', [
            'controller_name' => 'ArticleController',
            'article' => $article,
        ]);
    }

    private function prepareArticleData($request, $id = null)
    {
        $properties = [
            'slug' => 'setSlug',
            'title' => 'setTitle',
            'body' => 'setBody',
            'image' => 'setImage',
            'update_date' => 'setUpdateDate'
        ];

        if ($id != null) {
            $article = $this->getArticleData($id);
            $assignedTags = $article->getTag();
        } else {
            $article = new Articles();
            $assignedTags = false;
            $properties['create_date'] = 'setCreateDate';
        }

        $generalAdmin = new GeneralAdmin();
        $entityArticle = $generalAdmin->prepareData($request, $article, $properties, 'title');

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(Users::class)->find($request->request->get('user'));
        $entityArticle->setUser($user);

        $category = $entityManager->getRepository(Categories::class)->find($request->request->get('category'));
        $entityArticle->setCategory($category);

        $tags = $request->request->get('tags');
        if ($tags) {
            $repositoryTag = $entityManager->getRepository(Tags::class);

            if ($assignedTags) {
                foreach ($assignedTags as $assignedTag) {
                    $entityArticle->removeTag($assignedTag);
                }
            }

            foreach ($tags as $tag_id) {
                $tag = $repositoryTag->find($tag_id);
                if ($tag) {
                    $entityArticle->addTag($tag);
                }
            }
        }

        return $entityArticle;
    }

    private function getArticleData($id)
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
