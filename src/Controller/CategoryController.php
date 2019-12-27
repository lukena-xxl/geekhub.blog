<?php

namespace App\Controller;

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
 * @package App\Controller
 * @Route("/category", name="category")
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
            'action' => $this->generateUrl('category_show_all'),
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

        return $this->render('categories/show_all.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
            'form_sort' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function showCategory($id)
    {
        return $this->render('categories/show.html.twig', [
            'controller_name' => 'CategoryController',
            'category' => $this->findCategory($id),
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
            'action' => $this->generateUrl('category_add'),
            'method' => 'post',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $category = new Categories();
            $category->setTitle($formData['title']);
            $category->setDescription($formData['description']);

            if (!empty($formData['slug'])) {
                $category->setSlug($formData['slug']);
            }

            !empty($formData['is_visible']) ? $i = 1 : $i = 0;
            $category->setIsVisible($i);

            $entityManager->persist($category);
            $entityManager->flush();

            $message = "Добавлена новая категория \"" . $category->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('category_show_all');
        }

        return $this->render('categories/add.html.twig', [
            'controller_name' => 'CategoryController',
            'form_add' => $form->createView(),
            'title' => 'Добавление категории',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param $id
     * @return Response
     */
    public function editCategory(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $category = $this->findCategory($id);

        $form = $this->createForm(CategoryAddType::class, $category, [
            'action' => $this->generateUrl('category_edit', ['id' => $category->getId()]),
            'method' => 'post',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $entityManager->persist($formData);
            $entityManager->flush();

            $message = "Категория была спешно изменена!";
            $this->addFlash('success', $message);
        }

        return $this->render('categories/add.html.twig', [
            'controller_name' => 'CategoryController',
            'form_add' => $form->createView(),
            'title' => 'Редактирование категории "' . $category->getTitle() . '"',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param UpdateManager $updateManager
     * @param LoggerInterface $logger
     * @param $id
     * @return Response
     */
    public function deleteCategory(UpdateManager $updateManager, LoggerInterface $logger, $id)
    {
        $category = $this->findCategory($id);

        if ($category->getArticles()->count() > 0) {
            throw $this->createNotFoundException(
                'К категории привязаны некоторые статьи. Уберите привязку и повторите попытку удаления!'
            );
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();

            $message = "Категория \"" . $category->getTitle() . "\" была удалена";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('category_show_all');
        }
    }

    /**
     * @param $id
     * @return Categories
     */
    private function findCategory($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Categories::class)->find($id);
        if (!$category) {
            throw $this->createNotFoundException(
                'Категория с идентификатором "' . $id . '" не найдена!'
            );
        } else {
            return $category;
        }
    }
}
