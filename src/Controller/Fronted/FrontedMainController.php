<?php

namespace App\Controller\Fronted;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FrontedMainController extends AbstractController
{
    /**
     * @Route("/fronted", name="fronted_index")
     */
    public function index()
    {
        return $this->render('fronted/base.html.twig', [
            'controller_name' => 'FrontedMainController',
        ]);
    }
}
