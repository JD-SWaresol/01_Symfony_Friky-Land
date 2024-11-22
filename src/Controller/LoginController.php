<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginController extends AbstractController
{


    private $em;

    /**
     * @param $em
     */

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack) 
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }


    #[Route('/login', name: 'login')]
    public function index(AuthenticationUtils $authenticationUtils, Request $request): Response
    {

        // Instancia las entidades para una session
        $user = new User();

        // Obtiene un error de logeo si es que existe
        $error = $authenticationUtils->getLastAuthenticationError();

        // Ultimo nombre de usuario ingresado por el usuario
        $lastUsername = $authenticationUtils->getLastUsername();


        // Buscamos y validamos que exista el usuario en base al email
        $user = $this->em->getRepository(User::class)->findUser($lastUsername);
        

        $session = 'Sin Session!!';
        $email = 'No existe!!!';

        if ($user) {
            $email = $lastUsername;
            //
            $session = $this->requestStack->getSession();
            $session->start();
            $session->set('email-login', $email);

            // return $this->redirectToRoute('index', [
            // ]);
        }


        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
            'email'         => $session
        ]);
    }


    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();

        $session->invalidate();

        throw new \Exception('Don\'t forget to activate logout in security.yaml');

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error
        ]);
    }
}
