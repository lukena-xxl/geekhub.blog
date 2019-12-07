<?php


namespace App\Controller;


use App\Entity\Statuses;
use App\Form\Statuses\StatusType;
use App\Repository\StatusesRepository;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
            'status' => $this->getStatusData($id),
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
        $form = $this->createForm(StatusType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $status = new Statuses();
            $status->setTitle($formData['title']);

            $entityManager->persist($status);
            $entityManager->flush();

            $message = "Добавлен новый статус пользователя \"" . $status->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('status_show_all');
        }

        return $this->render('statuses/add.html.twig', [
            'controller_name' => 'StatusController',
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
    public function editStatus(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {

    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param UpdateManager $updateManager
     * @param $id
     * @return Response
     */
    public function deleteStatus(UpdateManager $updateManager, $id)
    {
        $status = $this->getStatusData($id);

        if ($status->getUsers()->count() > 0) {
            throw $this->createNotFoundException(
                'К статусу привязаны некоторые пользователи. Уберите привязку и повторите попытку удаления!'
            );
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($status);
            $entityManager->flush();

            $message = "Статус пользователя с идентификатором \"" . $id . "\" был удален!";
            $updateManager->notifyOfUpdate($message);

            return $this->render('statuses/access_delete.html.twig', [
                'controller_name' => 'StatusController',
                'status' => $status,
            ]);
        }
    }

    private function getStatusData($id)
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
