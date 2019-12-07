<?php


namespace App\Controller;


use App\Entity\Articles;
use App\Entity\Tags;
use App\Form\Tags\TagType;
use App\Repository\TagsRepository;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
     * @param Request $request
     * @param TagsRepository $tagsRepository
     * @return Response
     */
    public function showAllTags(Request $request, TagsRepository $tagsRepository)
    {
        $arguments = [];

        if ($request->query->has('sort')) {
            $sorting = $request->query->get('sort');

            if (!empty($sorting['article'])) {
                $arguments['article'] = $sorting['article'];
            }
        }

        if (count($arguments) > 0) {
            $tags = $tagsRepository->findBySort($arguments);
        } else {
            $tags = $tagsRepository->findAll();
        }

        $entityManager = $this->getDoctrine()->getManager();
        $articles = $entityManager->getRepository(Articles::class)->findAll();

        return $this->render('tags/show_all.html.twig', [
            'controller_name' => 'TagController',
            'tags' => $tags,
            'articles' => $articles,
            'arguments' => $arguments,
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
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param UpdateManager $updateManager
     * @return Response
     */
    public function addTags(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, UpdateManager $updateManager)
    {
        $form = $this->createForm(TagType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $tag = new Tags();
            $tag->setTitle($formData['title']);

            if (!empty($formData['slug'])) {
                $tag->setSlug($formData['slug']);
            }

            $entityManager->persist($tag);
            $entityManager->flush();

            $message = "Добавлен новый тег \"" . $tag->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('tag_show_all');
        }

        return $this->render('tags/add.html.twig', [
            'controller_name' => 'TagController',
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
    public function editTag(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {

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
