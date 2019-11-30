<?php


namespace App\Controller;

use App\Entity\Categories;
use App\Model\GeneralAdmin;
use App\Repository\CategoriesRepository;
use App\Services\UpdateManager;
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
     * @param CategoriesRepository $categoriesRepository
     * @return Response
     */
    public function showAllCategories(CategoriesRepository $categoriesRepository)
    {
        $categories = $categoriesRepository->findAll();

        return $this->render('categories/show_all.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
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
     * @param ValidatorInterface $validator
     * @param UpdateManager $updateManager
     * @return Response
     */
    public function addCategory(Request $request, ValidatorInterface $validator, UpdateManager $updateManager)
    {
        if ($request->request->has('title')) {
            $category = $this->prepareCategoryData($request);

            $errors = $validator->validate($category);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($category);
                $entityManager->flush();

                $message = "Добавлена новая категория \"" . $category->getTitle() . "\"";
                $updateManager->notifyOfUpdate($message);

                return $this->render('categories/access_add.html.twig', [
                    'controller_name' => 'CategoryController',
                    'category' => $category,
                ]);
            }
        } else {
            return $this->render('categories/add.html.twig', [
                'controller_name' => 'CategoryController',
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
    public function editCategory(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {
        if ($request->request->has('id')) {
            $category = $this->prepareCategoryData($request, $id);

            $errors = $validator->validate($category);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($category);
                $entityManager->flush();

                $message = "Категория с идентификатором \"" . $category->getId() . "\" была изменена!";
                $updateManager->notifyOfUpdate($message);

                return $this->render('categories/show.html.twig', [
                    'controller_name' => 'CategoryController',
                    'category' => $category,
                ]);
            }
        } else {
            return $this->render('categories/edit.html.twig', [
                'controller_name' => 'CategoryController',
                'category' => $this->getCategoryData($id),
            ]);
        }
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

    private function prepareCategoryData($request, $id = null)
    {
        if ($id!=null) {
            $category = $this->getCategoryData($id);
        } else {
            $category = new Categories();
        }

        $properties = [
            'slug' => 'setSlug',
            'title' => 'setTitle',
            'description' => 'setDescription'
        ];

        $generalAdmin = new GeneralAdmin();

        return $generalAdmin->prepareData($request, $category, $properties, 'title');
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
