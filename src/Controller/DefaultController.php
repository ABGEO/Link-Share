<?php

namespace App\Controller;

use App\Entity\URLPacks;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class DefaultController extends AbstractController
{
    /**
     * Homepage.
     *
     * @return Response
     */
    public function index()
    {
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * Add new URL's Pack in DB.
     *
     * @param Request $request
     * @param Security $security
     *
     * @return Response
     */
    public function createPack(Request $request, Security $security)
    {
        //Abort if not authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        //Get data from request
        $request = $request->request;

        $token = $request->get('token');
        $description = $request->get('description');
        $packs = $request->get('packs');

        //Check token
        if ($this->isCsrfTokenValid('share-links', $token)) {
            //Get Doctrine and Entity Manager
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();

            //Get User repo
            $userRepo = $em->getRepository(User::class);

            //Get current user
            $user = $userRepo->findOneBy(['username' => $security->getUser()->getUsername()]);

            //Create new pack
            $URLPacks = new URLPacks();

            $URLPacks->setUser($user)
                ->setDescription($description)
                ->setLinks($packs)
                ->setUniqLink(md5(time().$description.uniqid()));

            //Save in DB
            $em->persist($URLPacks);
            $em->flush();

            $response = array(
                'status' => 'created',
                'additions' => $URLPacks->getUniqLink()
            );
        } else {
            $response = array(
                'status' => 'invalidToken',
                'additions' => null
            );
        }

        return new Response(json_encode($response));
    }
}
