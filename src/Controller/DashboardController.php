<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Entity\Comentarios;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $user = $this->getUser(); //Se obtiene al usuario actualmente loqueado
        if($user){
            $em = $this->getDoctrine()->getManager();
            $query = $em->getRepository(Posts::class)->BuscarTodosLosPost();
            //se consulta los comentarios del usuario
            $comentarios = $em->getRepository(Comentarios::class)->BuscarComentarios($user->getId());
            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                4 /*limit per page*/
            );
            return $this->render('dashboard/index.html.twig', [
                'pagination' => $pagination,
                'comentarios' => $comentarios
            ]);

        }else{
            return $this->redirectToRoute('app_login');
        }

    }
}
