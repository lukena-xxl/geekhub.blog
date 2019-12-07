<?php


namespace App\Controller;

use App\Entity\Categories;
use App\Form\Categories\CategoryType;
use App\Repository\CategoriesRepository;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        $arguments = [];

        if ($request->query->has('sort')) {
            $sorting = $request->query->get('sort');

            if (!empty($sorting['num'])) {
                $arguments['num'] = $sorting['num'];
                $arguments['symbol'] = $sorting['symbol'];
            }
        }

        if (count($arguments) > 0) {
            $categories = $categoriesRepository->findBySort($arguments);
        } else {
            $categories = $categoriesRepository->findAll();
        }

        return $this->render('categories/show_all.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
            'arguments' => $arguments,
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
            'category' => $this->getCategoryData($id),
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
        $form = $this->createForm(CategoryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $category = new Categories();
            $category->setTitle($formData['title']);
            $category->setDescription($formData['description']);

            if (!empty($formData['slug'])) {
                $category->setSlug($formData['slug']);
            }

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
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UpdateManager $updateManager
     * @param $id
     */
    public function editCategory(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {

    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param UpdateManager $updateManager
     * @param $id
     * @return Response
     */
    public function deleteCategory(UpdateManager $updateManager, $id)
    {
        $category = $this->getCategoryData($id);

        if ($category->getArticles()->count() > 0) {
            throw $this->createNotFoundException(
                'К категории привязаны некоторые статьи. Уберите привязку и повторите попытку удаления!'
            );
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();

            $message = "Категория с идентификатором \"" . $id . "\" была удалена!";
            $updateManager->notifyOfUpdate($message);

            return $this->render('categories/access_delete.html.twig', [
                'controller_name' => 'CategoryController',
                'category' => $category,
            ]);
        }
    }

    private function getCategoryData($id)
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
