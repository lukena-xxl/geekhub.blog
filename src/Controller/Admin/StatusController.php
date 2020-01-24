<?php


namespace App\Controller\Admin;


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
 * @package App\Controller\Admin
 * @Route("/admin/status", name="admin_status")
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

        return $this->render('admin/statuses/show_all.html.twig', [
            'controller_name' => 'StatusController',
            'statuses' => $statuses,
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param Statuses $status
     * @return Response
     */
    public function showStatus(Statuses $status)
    {
        return $this->render('admin/statuses/show.html.twig', [
            'controller_name' => 'StatusController',
            'status' => $status,
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
            'action' => $this->generateUrl('admin_status_add'),
            'method' => 'post',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $form->getData();
            $status->setTitle(mb_strtoupper($status->getTitle(), 'UTF-8'));

            $entityManager->persist($status);
            $entityManager->flush();

            $message = "Added a new status of \"" . $status->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('admin_status_show_all');
        }

        return $this->render('admin/statuses/add.html.twig', [
            'controller_name' => 'StatusController',
            'form_add' => $form->createView(),
            'title' => 'Adding status',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param Statuses $status
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function editStatus(Statuses $status, Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(StatusAddType::class, $status, [
            'action' => $this->generateUrl('admin_status_edit', ['id' => $status->getId()]),
            'method' => 'post',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status->setTitle(mb_strtoupper($status->getTitle(), 'UTF-8'));

            $entityManager->persist($status);
            $entityManager->flush();

            $message = "Status has been successfully changed!";
            $this->addFlash('success', $message);
        }

        return $this->render('admin/statuses/add.html.twig', [
            'controller_name' => 'StatusController',
            'form_add' => $form->createView(),
            'title' => 'Editing the status "' . $status->getTitle() . '"',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param Statuses $status
     * @param UpdateManager $updateManager
     * @param LoggerInterface $logger
     * @return Response
     */
    public function deleteStatus(Statuses $status, UpdateManager $updateManager, LoggerInterface $logger)
    {
        if ($status->getUsers()->count() > 0) {
            $this->addFlash('warning', 'Some users have this status. Define a different status for them and try to delete again!');
            return $this->redirectToRoute('admin_status_show', ['id' => $status->getId()]);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($status);
            $entityManager->flush();

            $message = "The status \"" . $status->getTitle() . "\" has been deleted";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('admin_status_show_all');
        }
    }
}
