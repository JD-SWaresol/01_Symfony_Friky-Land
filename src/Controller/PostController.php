<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

class PostController extends AbstractController
{


    private $em;

    /**
     * @param $em
     */

    public function __construct(EntityManagerInterface $em) 
    {
        $this->em = $em;
    }


    // Verifica que el usuario este autenticado antes de acceder
    public function isAuthenticated(){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    }

    public function is_Authenticated(Request $request): ?Session
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        if ( $request->getSession() )
        {
            // Obtenemos la sesion iniciada
            $session = $request->getSession();
        }
        else 
        {
            return $this->redirectToRoute('login');
        }

        return $session;
    }

    #[Route('/', name: 'index')]
    public function index(Request $request, SluggerInterface $slugger, PaginatorInterface $paginator): Response
    {

        $this->isAuthenticated();


        if ( $request->getSession() )
        {
            // Obtenemos la sesion iniciada
            $session = $request->getSession();
        }
        else 
        {
            return $this->redirectToRoute('login');
        }

        // Obtiene la información sobre los atributos de seguridad '_security'
        $session_info = $session->all();
        
        // Guardamos el 'email' del array que contiene los atributos de seguridad
        $user_email = $session_info['_security.last_username'];
        
        // Consultamos las Propiedades del usuario en base al Email
        $query_user = $this->em->getRepository(User::Class)->findUser($user_email);

        // Extraemos el identificador del usuario
        $user_id = $query_user[0]['id'];

        // Instanciamos la entidad
        $post = new Post();
        
        // Generamos un dql para la parte de la paginacion
        $query = $this->em->getRepository(Post::class)->findAllPosts();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            3 /* limit per page */
        );
    

        //Llamamos el formulario que hemos creado para la entidad
        $form = $this->createForm(PostType::class, $post);
        
        //Obtenemos la peticion pasando el request de los parametros de la función
        $form->handleRequest($request);

        

        //Validamos que se envio la la info y que además sea valida
        if ($form->isSubmitted() && $form->isValid())
        {
            //Cargamos el archivo de la foto
            $file = $form->get('file')->getData();

            //Remplazamos los espacios del titulo por guiones
            $url = str_replace(" ", "-", $form->get('title')->getData());
            
            if($file)
            {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    //Mueve el archivo a un lugar en especifico (es el parametro uno)
                    $file->move('files_directory', $newFilename);
                } catch (FileException $e) {
                    throw new \Exception('Ups hay un problema con el archivo!!');
                }

                $post->setFile($newFilename);

            }

            // Asignamos la URL de forma automatica
            $post->setUrl($url);


            //Indicamos que usuario realizará el post
            $user = $this->em->getRepository(User::class)->find($user_id);
            
            // Asigamos al usuario dentro del post form
            $post->setUser($user);
            $this->em->persist($post);
            $this->em->flush();
            
            // Redireccionamos hacia el nombre de la ruta
            return $this->redirectToRoute('index');
        }

        dump($session);
        dump($form);
        return $this->render('post/index.html.twig', [
            // Enviamos el formulario para renderizarlo en la view
            'form' => $form->createView(),
            'posts' => $pagination,
            'email' => $session
        ]);
    }


    #[Route('/post/details/{id}', name: 'postDetails')]
    public function postDetails(Request $request, Post $post, int $id)
    {
        // $this->isAuthenticated();

        $session_info = $this->is_Authenticated($request);

        $post = $this->em->getRepository(Post::class)->find($id);
        return $this->render('post/details.html.twig', [
            'post' => $post,
            'session_info' => $session_info
        ]);
    }


    // NOTA: Actualiza la interfaz y el controler para que se pueda actualizar esta info
    #[Route('/post/edit/{id}', name: 'postEdit')]
    public function postEdit(Request $request, Post $post, int $id)
    {
        $session_info = $this->is_Authenticated($request);

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'session_info' => $session_info
        ]);
    }


    #[Route('/post/delete/{id}', name: 'postDelete')]
    public function postDelete(Request $request, Post $post, int $id)
    {
        // #[MapEntity(mapping: ['id' => 'id'])] Post $post

        $session_info = $this->is_Authenticated($request);

        $post = $this->em->getRepository(Post::class)->find($id);
        $this->em->remove($post);
        $this->em->flush();// Escribe el nuevo registro dentro de la base de datos

        return $this->redirectToRoute('index');
    }
}
