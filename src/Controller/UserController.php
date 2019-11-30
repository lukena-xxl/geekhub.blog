<?php


namespace App\Controller;


use App\Entity\Categories;
use App\Entity\Statuses;
use App\Entity\Users;
use App\Model\GeneralAdmin;
use App\Repository\UsersRepository;
use App\Services\UpdateManager;
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
        if ($request->query->has('arg1')) {
            $arguments = [
                'arg1' => $request->query->get('arg1'),
                'arg2' => $request->query->get('arg2'),
                'arg3' => $request->query->get('arg3')
            ];

            $users = $usersRepository->findBySort($arguments);
        } else {
            $arguments = [
                'arg1' => 'b',
                'arg2' => 0,
                'arg3' => ''
            ];

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
     * @param ValidatorInterface $validator
     * @param UpdateManager $updateManager
     * @return Response
     */
    public function addUser(Request $request, ValidatorInterface $validator, UpdateManager $updateManager)
    {
        if ($request->request->has('login')) {
            $user = $this->prepareUserData($request);

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $message = "Добавлен новый пользователь \"" . $user->getLogin() . "\" со статусом \"" . $user->getStatus()->getTitle() . "\"";
                $updateManager->notifyOfUpdate($message);

                return $this->render('users/access_add.html.twig', [
                    'controller_name' => 'UserController',
                    'user' => $user,
                ]);
            }
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $statuses = $entityManager->getRepository(Statuses::class)->findAll();

            return $this->render('users/add.html.twig', [
                'controller_name' => 'UserController',
                'statuses' => $statuses,
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
    public function editUser(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {
        if ($request->request->has('id')) {
            $user = $this->prepareUserData($request, $id);

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $message = "Данные пользователя с идентификатором \"" . $user->getId() . "\" были изменены!";
                $updateManager->notifyOfUpdate($message);

                return $this->render('users/show.html.twig', [
                    'controller_name' => 'UserController',
                    'user' => $user,
                ]);
            }

        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $statuses = $entityManager->getRepository(Statuses::class)->findAll();

            return $this->render('users/edit.html.twig', [
                'controller_name' => 'UserController',
                'user' => $this->getUserData($id),
                'statuses' => $statuses,
            ]);
        }
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

    private function prepareUserData($request, $id = null)
    {
        $properties = [
            'login' => 'setLogin',
            'password' => 'setPassword',
            'name' => 'setName'
        ];

        if ($id!=null) {
            $user = $this->getUserData($id);

            if(empty($request->request->get('password'))) {
                $request->request->set('password', $user->getPassword());
            }
        } else {
            $user = new Users();
            $properties['registration_date'] = 'setRegistrationDate';
        }

        $generalAdmin = new GeneralAdmin();
        $entityUser = $generalAdmin->prepareData($request, $user, $properties);

        $entityManager = $this->getDoctrine()->getManager();
        $status = $entityManager->getRepository(Statuses::class)->find($request->request->get('status'));
        $entityUser->setStatus($status);

        return $entityUser;
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
