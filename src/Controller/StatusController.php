<?php


namespace App\Controller;


use App\Entity\Statuses;
use App\Model\GeneralAdmin;
use App\Repository\StatusesRepository;
use App\Services\UpdateManager;
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
     * @param ValidatorInterface $validator
     * @param UpdateManager $updateManager
     * @return Response
     */
    public function addStatus(Request $request, ValidatorInterface $validator, UpdateManager $updateManager)
    {
        if ($request->request->has('title')) {
            $status = $this->prepareStatusData($request);

            $errors = $validator->validate($status);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($status);
                $entityManager->flush();

                $message = "Добавлен новый статус для пользователей \"" . $status->getTitle() . "\"";
                $updateManager->notifyOfUpdate($message);

                return $this->render('statuses/access_add.html.twig', [
                    'controller_name' => 'StatusController',
                    'status' => $status,
                ]);
            }
        } else {
            return $this->render('statuses/add.html.twig', [
                'controller_name' => 'UserController',
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
    public function editStatus(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {
        if ($request->request->has('id')) {
            $status = $this->prepareStatusData($request, $id);

            $errors = $validator->validate($status);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($status);
                $entityManager->flush();

                $message = "Статус с идентификатором \"" . $status->getId() . "\" был изменен!";
                $updateManager->notifyOfUpdate($message);

                return $this->render('statuses/show.html.twig', [
                    'controller_name' => 'StatusController',
                    'status' => $status,
                ]);
            }
        } else {
            return $this->render('statuses/edit.html.twig', [
                'controller_name' => 'StatusController',
                'status' => $this->getStatusData($id),
            ]);
        }
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

    private function prepareStatusData($request, $id = null)
    {
        if ($id!=null) {
            $status = $this->getStatusData($id);
        } else {
            $status = new Statuses();
        }

        $properties = [
            'title' => 'setTitle'
        ];

        $generalAdmin = new GeneralAdmin();

        return $generalAdmin->prepareData($request, $status, $properties);
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
