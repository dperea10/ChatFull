<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class RegistroController extends AbstractController
{
    /**
     * @Route("/registro", name="registro")
     */
    public function index()
    {
        $user = new User();
        $form = $this->createform(UserType::class, $user);
        return $this->render('registro/index.html.twig', [
            'controller_name' => 'RegistroController',
            'formulario'=> $form->createView()
        ]);
    }
}
