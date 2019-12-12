<?php


namespace App\Controller;


use App\Entity\Statuses;
use App\Form\Statuses\StatusAddType;
use App\Repository\StatusesRepository;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StatusController
 * @package App\Controller
 * @Route("/status", name="status")
 */
class StatusController extends AbstractController
{
    /**
     * @Route("/show", name="_show_all")
     * @param StatusesRepository $statusesRepository
     * @return Response
     */
    public function showAllStatuses(StatusesRepository $statusesRepository)
    {
        $statuses = $statusesRepository->findAll();

        return $this->render('statuses/show_all.html.twig', [
            'controller_name' => 'StatusController',
            'statuses' => $statuses,
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function showStatus($id)
    {
        return $this->render('statuses/show.html.twig', [
            'controller_name' => 'StatusController',
            'status' => $this->findStatus($id),
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
    public function addStatus(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, UpdateManager $updateManager)
    {
        $form = $this->createForm(StatusAddType::class, null, [
            'action' => $this->generateUrl('status_add'),
            'method' => 'post',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $status = new Statuses();
            $status->setTitle($formData['title']);

            $entityManager->persist($status);
            $entityManager->flush();

            $message = "Добавлен новый статус \"" . $status->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('status_show_all');
        }

        return $this->render('statuses/add.html.twig', [
            'controller_name' => 'StatusController',
            'form_add' => $form->createView(),
            'title' => 'Добавление статуса',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param $id
     * @return Response
     */
    public function editStatus(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $status = $this->findStatus($id);

        $form = $this->createForm(StatusAddType::class, $status, [
            'action' => $this->generateUrl('status_edit', ['id' => $status->getId()]),
            'method' => 'post',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $entityManager->persist($formData);
            $entityManager->flush();

            $message = "Статус был успешно изменен!";
            $this->addFlash('success', $message);
        }

        return $this->render('statuses/add.html.twig', [
            'controller_name' => 'StatusController',
            'form_add' => $form->createView(),
            'title' => 'Редактирование статуса "' . $status->getTitle() . '"',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param UpdateManager $updateManager
     * @param LoggerInterface $logger
     * @param $id
     * @return Response
     */
    public function deleteStatus(UpdateManager $updateManager, LoggerInterface $logger, $id)
    {
        $status = $this->findStatus($id);

        if ($status->getUsers()->count() > 0) {
            throw $this->createNotFoundException(
                'К статусу привязаны некоторые пользователи. Уберите привязку и повторите попытку удаления!'
            );
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($status);
            $entityManager->flush();

            $message = "Статус \"" . $status->getTitle() . "\" был удален";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('status_show_all');
        }
    }

    /**
     * @param $id
     * @return Statuses
     */
    private function findStatus($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $status = $entityManager->getRepository(Statuses::class)->find($id);
        if (!$status) {
            throw $this->createNotFoundException(
                'Статус с идентификатором "' . $id . '" не найден!'
            );
        } else {
            return $status;
        }
    }
}
