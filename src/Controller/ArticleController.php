<?php


namespace App\Controller;


use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Tags;
use App\Entity\Users;
use App\Model\GeneralAdmin;
use App\Repository\ArticlesRepository;
use App\Services\UpdateManager;
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
     * @param ArticlesRepository $articlesRepository
     * @return Response
     */
    public function showAllArticles(ArticlesRepository $articlesRepository)
    {
        $articles = $articlesRepository->findAll();

        return $this->render('articles/show_all.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
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
     * @param ValidatorInterface $validator
     * @param UpdateManager $updateManager
     * @return Response
     */
    public function addArticle(Request $request, ValidatorInterface $validator, UpdateManager $updateManager)
    {
        if ($request->request->has('title')) {
            $article = $this->prepareArticleData($request);

            $errors = $validator->validate($article);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($article);
                $entityManager->flush();

                $message = "Добавлена новая публикация \"" . $article->getTitle() . "\"";
                $updateManager->notifyOfUpdate($message);

                return $this->render('articles/access_add.html.twig', [
                    'controller_name' => 'ArticleController',
                    'article' => $article,
                ]);
            }
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $categories = $entityManager->getRepository(Categories::class)->findAll();
            $tags = $entityManager->getRepository(Tags::class)->findAll();
            $users = $entityManager->getRepository(Users::class)->findAll();

            return $this->render('articles/add.html.twig', [
                'controller_name' => 'ArticleController',
                'categories' => $categories,
                'tags' => $tags,
                'users' => $users,
            ]);
        }
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UpdateManager $updateManager
     * @param $id
     * @return Response
     */
    public function editArticle(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {
        if ($request->request->has('id')) {
            $article = $this->prepareArticleData($request, $id);


            $errors = $validator->validate($article);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($article);
                $entityManager->flush();

                $message = "Публикация с идентификатором \"" . $article->getId() . "\" была изменена!";
                $updateManager->notifyOfUpdate($message);

                return $this->render('articles/show.html.twig', [
                    'controller_name' => 'ArticleController',
                    'article' => $article,
                ]);
            }



        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $categories = $entityManager->getRepository(Categories::class)->findAll();
            $tags = $entityManager->getRepository(Tags::class)->findAll();
            $users = $entityManager->getRepository(Users::class)->findAll();

            return $this->render('articles/edit.html.twig', [
                'controller_name' => 'ArticleController',
                'article' => $this->getArticleData($id),
                'categories' => $categories,
                'tags' => $tags,
                'users' => $users,
            ]);
        }
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

        if ($id!=null) {
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
