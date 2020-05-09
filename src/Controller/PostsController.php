<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Form\PostsType;
use App\Entity\Comentarios;
use App\Form\ComentarioType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostsController extends AbstractController
{

    /**
     * @Route("/registrar-posts", name="registrarposts")
     */
    public function index(Request $request)
    {
        
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $brochureFile = $form->get('foto')->getData();
             // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('fotos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exeption('message', 'Error al subir archivo');

                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $post->setFoto($newFilename);
            }

            //Se obtiene el usuario
            $user = $this->getUser();
            //Se envia el usuarios
            $post->setUser($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }       

        
        return $this->render('posts/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
    * @Route("/post/{id}", name="verpost")
    */

    public function VerPost($id, Request $request, PaginatorInterface $paginator){
        $em = $this->getDoctrine()->getManager();
        $comentario = new Comentarios();
        $post = $em->getRepository(Posts::class)->find($id);
        $queryComentarios = $em->getRepository(Comentarios::class)->BuscarComentariosDeUNPost($post->getId());
        $form = $this->createForm(ComentarioType::class, $comentario);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user = $this->getUser();
            $comentario->setPost($post);
            $comentario->setUser($user);
            $em->persist($comentario);
            $em->flush();
            $this->addFlash('exito', Comentarios::COMENTARIO_AGREGADO_EXITOSAMENTE);
            return $this->redirectToRoute('verpost',['id'=>$post->getId()]);
        }
        $pagination = $paginator->paginate(
            $queryComentarios, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            15 /*limit per page*/
        );
        return $this->render('posts/verpost.html.twig',['post'=>$post, 'form'=>$form->createView(), 'comentarios'=>$pagination]);
    }

    /**
    * @Route("/mis-post/", name="misposts")
    */

    public function MisPosts(){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $posts = $em->getRepository(Posts::class)->findBy(['user'=>$user]);
        return $this->render('posts/misposts.html.twig', ['posts'=>$posts]);
    }

    /**
    * @Route("/like", options={"expose"=true}, name="like")
    */

    public function Like(Request $request){
        if($request->isXmlHttpRequest()){
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $id = $request->request->get('id');
            $post = $em->getRepository(Posts::class)->find($id);
            $like = $post->getLikes();
            $like .= $user->getId().',';
            $post->setLikes($like);
            $em->flush();
            return new JsonResponse(['like'=>$like]);
        }else{
            throw new \Exception('No permitido');
        }
       
    }



}
