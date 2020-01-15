<?php

namespace App\Controller;

use App\Entity\Tags;
use App\Form\Tags\TagAddType;
use App\Form\Tags\TagSortType;
use App\Repository\TagsRepository;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TagController
 * @package App\Controller
 * @Route("/tag", name="tag")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/show", name="_show_all")
     * @param Request $request
     * @param TagsRepository $tagsRepository
     * @return Response
     */
    public function showAllTags(Request $request, TagsRepository $tagsRepository)
    {
        $form = $this->createForm(TagSortType::class, null, [
            'action' => $this->generateUrl('tag_show_all'),
            'method' => 'get',
        ]);

        $form->handleRequest($request);
        $arguments = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $arguments['article'] = $formData['article'];
        }

        if (count($arguments) > 0) {
            $tags = $tagsRepository->findBySort($arguments);
        } else {
            $tags = $tagsRepository->findAll();
        }

        return $this->render('tags/show_all.html.twig', [
            'controller_name' => 'TagController',
            'tags' => $tags,
            'form_sort' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param Tags $tag
     * @return Response
     */
    public function showTag(Tags $tag)
    {
        return $this->render('tags/show.html.twig', [
            'controller_name' => 'TagController',
            'tag' => $tag,
        ]);
    }

    /**
     * @Route("/add", name="_add")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param UpdateManager $updateManager
     * @return Response
     */
    public function addTags(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, UpdateManager $updateManager)
    {
        $form = $this->createForm(TagAddType::class, null, [
            'action' => $this->generateUrl('tag_add'),
            'method' => 'post',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $tag = new Tags();
            $tag->setTitle($formData['title']);

            if (!empty($formData['slug'])) {
                $tag->setSlug($formData['slug']);
            }

            !empty($formData['is_visible']) ? $i = 1 : $i = 0;
            $tag->setIsVisible($i);

            $entityManager->persist($tag);
            $entityManager->flush();

            $message = "Added a new tag \"" . $tag->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('tag_show_all');
        }

        return $this->render('tags/add.html.twig', [
            'controller_name' => 'TagController',
            'form_add' => $form->createView(),
            'title' => 'Adding a tag',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Tags $tag
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function editTag(Tags $tag, Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(TagAddType::class, $tag, [
            'action' => $this->generateUrl('tag_edit', ['id' => $tag->getId()]),
            'method' => 'post',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $entityManager->persist($formData);
            $entityManager->flush();

            $message = "The tag has been successfully modified!";
            $this->addFlash('success', $message);
        }

        return $this->render('tags/add.html.twig', [
            'controller_name' => 'TagController',
            'form_add' => $form->createView(),
            'title' => 'Editing the tag "' . $tag->getTitle() . '"',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param Tags $tag
     * @param UpdateManager $updateManager
     * @param LoggerInterface $logger
     * @return Response
     */
    public function deleteTag(Tags $tag, UpdateManager $updateManager, LoggerInterface $logger)
    {
        $articlesCollection = $tag->getArticles();
        if ($articlesCollection) {
            foreach ($articlesCollection as $article) {
                $article->removeTag($tag);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($tag);
        $entityManager->flush();

        $message = "The tag \"" . $tag->getTitle() . "\" has been removed";
        $logger->info($message);
        $updateManager->notifyOfUpdate($message);
        $this->addFlash('success', $message);

        return $this->redirectToRoute('tag_show_all');
    }
}
