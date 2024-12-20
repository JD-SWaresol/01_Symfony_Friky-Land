<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserController extends AbstractController
{

    private $em;

    /**
     * @param $em
     */
    public function __construct(EntityManagerInterface $em, private AccessDecisionManagerInterface $accessDecisionManager) 
    {
        $this->em = $em;
    }


    #[Route('/registration', name: 'userRegistration')]
    public function userRegistration(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $registration_form = $this->createForm(UserType::class, $user);
        $registration_form->handleRequest($request);
        if ($registration_form->isSubmitted() && $registration_form->isValid()){

            //Obtenemos el nombre del campo (password) de UserType
            $plaintextPassword = $registration_form->get('password')->getData();

            //Se hace la codificacion del password (Encripa la password)
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            //Edita la constraseña por una encriptada
            $user->setPassword($hashedPassword);

            //Asignamos un rol para el usuario
            $user->setRoles(['ROLE_USER']);
            $this->em->persist($user);
            $this->em->flush();
            return $this->redirectToRoute('userRegistration');
        }
        return $this->render('user/index.html.twig', [
            'registration_form' => $registration_form->createView()
        ]);
    }

    
    #[Route('/user/profile/{id}', name: 'userProfile')]
    public function userProfile(User $user, int $id)
    {


        $user = $this->em->getRepository(User::class)->find($id);
        return $this->render('user/user-profile-test.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/usersList', name: 'usersList')]
    public function getUsersList(TokenInterface $token){

        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {

            $users = new User();

            $users = $this->em->getRepository(User::class)->findAll();

            dump($users);

            return $this->render('user/user-list.html.twig', [
                'users' => $users
            ]);
        }

        // Redireccionamos hacia index en caso de que el usuario no sea Admin
        return $this->redirectToRoute('index');
    }

}
