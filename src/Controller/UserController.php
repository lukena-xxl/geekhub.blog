<?php


namespace App\Controller;


use App\Entity\Categories;
use App\Entity\Statuses;
use App\Entity\Users;
use App\Form\Users\UserType;
use App\Repository\UsersRepository;
use App\Services\UpdateManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/user", name="user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/show", name="_show_all")
     * @param Request $request
     * @param UsersRepository $usersRepository
     * @return Response
     */
    public function showAllUsers(Request $request, UsersRepository $usersRepository)
    {
        $arguments = [];

        if ($request->query->has('sort')) {
            $sorting = $request->query->get('sort');

            if (!empty($sorting['num'])) {
                $arguments['num'] = $sorting['num'];
                $arguments['symbol'] = $sorting['symbol'];
            }

            if (!empty($sorting['category'])) {
                $arguments['category'] = $sorting['category'];
            }
        }

        if (count($arguments) > 0) {
            $users = $usersRepository->findBySort($arguments);
        } else {
            $users = $usersRepository->findAll();
        }

        $entityManager = $this->getDoctrine()->getManager();
        $categories = $entityManager->getRepository(Categories::class)->findAll();

        return $this->render('users/show_all.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
            'categories' => $categories,
            'arguments' => $arguments,
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function showUser($id)
    {
        return $this->render('users/show.html.twig', [
            'controller_name' => 'UserController',
            'user' => $this->getUserData($id),
        ]);
    }

    /**
     * @Route("/add", name="_add")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param UpdateManager $updateManager
     * @return Response
     * @throws Exception
     */
    public function addUser(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, UpdateManager $updateManager)
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $user = new Users();
            $user->setName($formData['name']);
            $user->setLogin($formData['login']);
            $user->setPassword($formData['password']);
            $user->setRegistrationDate(new \DateTime());

            $status = $entityManager->getRepository(Statuses::class)->find($formData['status']);
            if ($status) {
                $user->setStatus($status);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $message = "Добавлен новый пользователь \"" . $user->getLogin() . "\" со статусом \"" . $user->getStatus()->getTitle() . "\"";
            $logger->info($message);
            $updateManager->notifyOfUpdate($message);
            $this->addFlash('success', $message);

            return $this->redirectToRoute('user_show_all');
        }

        return $this->render('users/add.html.twig', [
            'controller_name' => 'UserController',
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
    public function editUser(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {

    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param UpdateManager $updateManager
     * @param $id
     * @return Response
     */
    public function deleteUser(UpdateManager $updateManager, $id)
    {
        $user = $this->getUserData($id);

        if ($user->getArticles()->count() > 0) {
            throw $this->createNotFoundException(
                'К пользователю привязаны некоторые статьи. Уберите привязку и повторите попытку удаления!'
            );
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            $message = "Пользователь с идентификатором \"" . $id . "\" был удален!";
            $updateManager->notifyOfUpdate($message);

            return $this->render('users/access_delete.html.twig', [
                'controller_name' => 'UserController',
                'user' => $user,
            ]);
        }
    }

    private function getUserData($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(Users::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException(
                'Пользователь с идентификатором "' . $id . '" не найден!'
            );
        } else {
            return $user;
        }
    }
}
