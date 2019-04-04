<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{
    /**
     * Register new user.
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        //Get data from request
        $request = $request->request;

        $token = $request->get('token');
        $email = $request->get('email');
        $password = $request->get('password');

        //Check token
        if ($this->isCsrfTokenValid('sign-up', $token)) {
            //Get Doctrine and Entity Manager
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();

            //Check if user exists
            $userRepo = $em->getRepository(User::class);
            if ($userRepo->userExists($email)) {
                $response = 'userExists';
            } else {
                //Create new User object
                $user = new User();

                //Generate unique username from email
                $username = substr(md5($email), 0, 16);

                //Set User info
                $user->setEmail($email);
                $user->setUsername($username);
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $password
                    )
                );

                //Save user in DB
                $em->persist($user);
                $em->flush();

                $response = 'successReg';
            }
        } else {
            $response = 'invalidToken';
        }

        return new Response($response);
    }
}
