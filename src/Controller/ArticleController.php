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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

            $arrArg = ['user', 'category', 'tag', 'from', 'to'];

            foreach ($arrArg as $arg) {
                if (!empty($formData[$arg])) {

                    $value = $formData[$arg];

                    if ($arg == 'to') {
                        $value = $formData[$arg]->setTime(23, 59, 59);
                    }

                    $arguments[$arg] = $value;
                }
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
     * @param Articles $article
     * @return Response
     */
    public function showArticle(Articles $article)
    {
        return $this->render('articles/show.html.twig', [
            'controller_name' => 'ArticleController',
            'article' => $article,
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
            'attr' => [
                'id' => 'article_form',
                'data-article' => '',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $response = [];

            $formData = $form->getData();
            $datetime = new \DateTime();

            $article = new Articles();
            $article->setTitle($formData['title']);
            $article->setBody($formData['body']);
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

            /** @var UploadedFile $file */
            $file = $form['image']->getData();

            if ($file) {
                $newFilename = $this->getNewFileName($file);

                try {
                    $file->move(
                        $this->getParameter('article_image_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $response['error'] = $e->getMessage();
                }

                if (!isset($response['error'])) {
                    $article->setImage($newFilename);

                    $entityManager->persist($article);
                    $entityManager->flush();

                    $message = "Added new publication \"" . $article->getTitle() . "\" in the category \"" . $article->getCategory()->getTitle() . "\"";
                    $logger->info($message);
                    $updateManager->notifyOfUpdate($message);

                    $response['id'] = $article->getId();
                }
            } else {
                $response['error'] = "Choose an image";
            }

            return $this->json(json_encode($response));
        }

        return $this->render('articles/add.html.twig', [
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
        $form = $this->createForm(ArticleAddType::class, $article, [
            'action' => $this->generateUrl('article_edit', ['id' => $article->getId()]),
            'method' => 'post',
            'attr' => [
                'id' => 'article_form',
                'data-article' => $article->getId(),
            ],
        ])->remove('create_date');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $article->setUpdateDate(new \DateTime());

            /** @var UploadedFile $file */
            $file = $form['image']->getData();

            if ($file) {
                $newFilename = $this->getNewFileName($file);

                try {
                    $file->move(
                        $this->getParameter('article_image_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $response['error'] = $e->getMessage();
                }

                if (!isset($response['error'])) {
                    $old_file = $article->getImage();

                    if (!empty($old_file) && file_exists($this->getParameter('article_image_dir') . "/" . $old_file)) {
                        unlink($this->getParameter('article_image_dir') . "/" . $old_file);
                    }

                    $article->setImage($newFilename);
                }
            }

            if (!isset($response['error'])) {
                $entityManager->persist($formData);
                $entityManager->flush();

                $response['id'] = $article->getId();
            }

            return $this->json(json_encode($response));
        }

        return $this->render('articles/add.html.twig', [
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
        $assignedTags = $article->getTag();
        if ($assignedTags) {
            foreach ($assignedTags as $assignedTag) {
                $article->removeTag($assignedTag);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        $message = "Publication \"" . $article->getTitle() . "\" has been deleted";
        $logger->info($message);
        $updateManager->notifyOfUpdate($message);
        $this->addFlash('success', $message);

        return $this->redirectToRoute('article_show_all');
    }

    /**
     * @param $file
     * @return string
     */
    private function getNewFileName($file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        return $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
    }
}
