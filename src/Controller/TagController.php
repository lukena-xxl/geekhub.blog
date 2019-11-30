<?php


namespace App\Controller;


use App\Entity\Tags;
use App\Model\GeneralAdmin;
use App\Repository\TagsRepository;
use App\Services\UpdateManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class TagController
 * @package App\Controller
 * @Route("/tag", name="tag")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/show", name="_show_all")
     * @param TagsRepository $tagsRepository
     * @return Response
     */
    public function showAllTags(TagsRepository $tagsRepository)
    {
        $tags = $tagsRepository->findAll();

        return $this->render('tags/show_all.html.twig', [
            'controller_name' => 'TagController',
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function showTag($id)
    {
        return $this->render('tags/show.html.twig', [
            'controller_name' => 'TagController',
            'tag' => $this->getTagData($id),
        ]);
    }

    /**
     * @Route("/add", name="_add")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UpdateManager $updateManager
     * @return Response
     */
    public function addTags(Request $request, ValidatorInterface $validator, UpdateManager $updateManager)
    {
        if ($request->request->has('title')) {
            $tag = $this->prepareTagData($request);

            $errors = $validator->validate($tag);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($tag);
                $entityManager->flush();

                $message = "Добавлен новый тег \"" . $tag->getTitle() . "\"";
                $updateManager->notifyOfUpdate($message);

                return $this->render('tags/access_add.html.twig', [
                    'controller_name' => 'TagController',
                    'tag' => $tag,
                ]);
            }
        } else {
            return $this->render('tags/add.html.twig', [
                'controller_name' => 'TagController',
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
    public function editTag(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {
        if ($request->request->has('id')) {
            $tag = $this->prepareTagData($request, $id);

            $errors = $validator->validate($tag);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($tag);
                $entityManager->flush();

                $message = "Тег с идентификатором \"" . $tag->getId() . "\" была изменен!";
                $updateManager->notifyOfUpdate($message);

                return $this->render('tags/show.html.twig', [
                    'controller_name' => 'TagController',
                    'tag' => $tag,
                ]);
            }
        } else {
            return $this->render('tags/edit.html.twig', [
                'controller_name' => 'TagController',
                'tag' => $this->getTagData($id),
            ]);
        }
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param UpdateManager $updateManager
     * @param $id
     * @return Response
     */
    public function deleteTag(UpdateManager $updateManager, $id)
    {
        $tag = $this->getTagData($id);

        $articlesCollection = $tag->getArticles();
        if ($articlesCollection) {
            foreach ($articlesCollection as $article) {
                $article->removeTag($tag);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($tag);
        $entityManager->flush();

        $message = "Тег с идентификатором \"" . $id . "\" был удален!";
        $updateManager->notifyOfUpdate($message);

        return $this->render('tags/access_delete.html.twig', [
            'controller_name' => 'TagController',
            'tag' => $tag,
        ]);
    }

    private function prepareTagData($request, $id = null)
    {
        if ($id!=null) {
            $tag = $this->getTagData($id);
        } else {
            $tag = new Tags();
        }

        $properties = [
            'slug' => 'setSlug',
            'title' => 'setTitle'
        ];

        $generalAdmin = new GeneralAdmin();

        return $generalAdmin->prepareData($request, $tag, $properties, 'title');
    }

    private function getTagData($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tag = $entityManager->getRepository(Tags::class)->find($id);
        if (!$tag) {
            throw $this->createNotFoundException(
                'Тег с идентификатором "' . $id . '" не найден!'
            );
        } else {
            return $tag;
        }
    }
}
