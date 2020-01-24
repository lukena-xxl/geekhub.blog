<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\Categories\CategoryAddType;
use App\Form\Categories\CategorySortType;
use App\Repository\CategoriesRepository;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller\Admin
 * @Route("/admin/category", name="admin_category")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/show", name="_show_all")
     * @param Request $request
     * @param CategoriesRepository $categoriesRepository
     * @return Response
     */
    public function showAllCategories(Request $request, CategoriesRepository $categoriesRepository)
    {
        $form = $this->createForm(CategorySortType::class, null, [
            'action' => $this->generateUrl('admin_category_show_all'),
            'method' => 'get',
        ]);

        $form->handleRequest($request);
        $arguments = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $arguments['num'] = $formData['num'];
            $arguments['symbol'] = $formData['symbol'];
        }

        if (count($arguments) > 0) {
            $categories = $categoriesRepository->findBySort($arguments);
        } else {
            $categories = $categoriesRepository->findAll();
        }

        return $this->render('admin/categories/show_all.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
            'form_sort' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param Categories $category
     * @return Response
     */
    public function showCategory(Categories $category)
    {
        return $this->render('admin/categories/show.html.twig', [
            'controller_name' => 'CategoryController',
            'category' => $category,
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
    public function addCategory(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, UpdateManager $updateManager)
    {
        $form = $this->createForm(CategoryAddType::class, null, [
            'action' => $this->generateUrl('admin_category_add'),
            'method' => 'post',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $entityManager->persist($category);
            $entityManager->flush();

            $message = "Added a new category of \"" . $category->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('admin_category_show_all');
        }

        return $this->render('admin/categories/add.html.twig', [
            'controller_name' => 'CategoryController',
            'form_add' => $form->createView(),
            'title' => 'Adding a category',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Categories $category
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function editCategory(Categories $category, Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(CategoryAddType::class, $category, [
            'action' => $this->generateUrl('admin_category_edit', ['id' => $category->getId()]),
            'method' => 'post',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $message = "The category has been hastily changed";
            $this->addFlash('success', $message);
        }

        return $this->render('admin/categories/add.html.twig', [
            'controller_name' => 'CategoryController',
            'form_add' => $form->createView(),
            'title' => 'Editing the "' . $category->getTitle() . '" category',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param Categories $category
     * @param UpdateManager $updateManager
     * @param LoggerInterface $logger
     * @return Response
     */
    public function deleteCategory(Categories $category, UpdateManager $updateManager, LoggerInterface $logger)
    {
        if ($category->getArticles()->count() > 0) {
            $this->addFlash('warning', 'Some publications are included in this category. Exclude them from this category and try deleting again!');
            return $this->redirectToRoute('admin_category_show', ['id' => $category->getId()]);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();

            $message = "The \"" . $category->getTitle() . "\" category has been deleted";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('admin_category_show_all');
        }
    }
}
