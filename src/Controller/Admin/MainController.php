<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('admin/base.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
