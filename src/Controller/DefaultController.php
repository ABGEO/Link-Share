<?php

namespace App\Controller;

use App\Entity\URLPacks;
use App\Entity\User;
use Goutte\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
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
        return $this->render('default/index.html.twig');
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
        $urls = $request->get('packs');

        //Check token
        if ($this->isCsrfTokenValid('share-links', $token)) {
            //Get Doctrine and Entity Manager
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();

            //Get User repo
            $userRepo = $em->getRepository(User::class);

            //Get current user
            $user = $userRepo->findOneBy(['username' => $security->getUser()->getUsername()]);

            $urlsFull = array();
            foreach ($urls as $url) {
                $url['website'] = $this->_parseWebsite($url['url']);
                array_push($urlsFull, $url);
            }

            //Create new pack
            $URLPacks = new URLPacks();

            $URLPacks->setUser($user)
                ->setDescription($description)
                ->setLinks($urlsFull)
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

    /**
     * Get single pack.
     *
     * @param String $id
     *
     * @return Response
     */
    public function getPack($id)
    {
        //Get URLPacks repository
        $packRepo = $this->getDoctrine()
            ->getManager()
            ->getRepository(URLPacks::class);

        //Find pack
        if ($pack = $packRepo->findOneBy(['uniqLink' => $id])) {
            return $this->render('default/pack.html.twig', [
                'pack' => $pack,
            ]);
        }

        //Abort if it not found
        throw $this->createNotFoundException("URL Pack \"{$id}\" not Found or has been removed.");
    }

    /**
     * Get user packs.
     *
     * @param Security $security
     *
     * @return Response
     */
    public function myPacks(Security $security)
    {
        //Abort if not authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        //Get Doctrine and Entity Manager
        $em = $this->getDoctrine()
            ->getManager();

        //Get User repo
        $userRepo = $em->getRepository(User::class);

        //Get current user
        $user = $userRepo->findOneBy(['username' => $security->getUser()->getUsername()]);

        $userPacks = $user->getURLPacks();

        return $this->render('default/my_packs.html.twig', [
            'packs' => $userPacks->count() ? $userPacks : null
        ]);
    }

    /**
     * Remove pack.
     *
     * @param $id
     * @param Request $request
     *
     * @return Response
     */
    public function removePack($id, Request $request)
    {
        //Get token from request
        $token = $request->request->get('token');

        //Check token
        if ($this->isCsrfTokenValid('remove-pack', $token)) {
            //Get Doctrine and Entity Manager
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();

            //Get repository
            $packsRepo = $doctrine->getRepository(URLPacks::class);

            //Get pack for deleting
            $pack = $packsRepo->find($id);

            //Remove pack
            $em->remove($pack);
            $em->flush();

            $return = 'removed';
        } else {
            $return = 'invalidToken';
        }

        return new Response($return);
    }

    /**
     * Get additional info from website.
     *
     * @param string $url
     *
     * @return array
     */
    private function _parseWebsite(string $url): array
    {
        //Create new User-Agent
        $client = new Client();

        $response = $client->request('GET', $url);

        $crawler = new Crawler($response->html());

        $domain = parse_url($url, PHP_URL_HOST);
        $description = $crawler->filterXpath("//meta[@name='description']")->extract(array('content'));

        //Get website favicon
        $favicon = null;
        $favicon = @file_get_contents("https://www.google.com/s2/favicons?domain={$domain}");

        return [
            'domain' => $domain,
            'title' => $crawler->filterXPath('//head/title')->getNode(0)->textContent,
            'description' => isset($description[0]) ? $description[0] : null,
            'favicon' => $favicon == null ? '' : 'data:image/jpeg;base64,' . base64_encode($favicon)
        ];
    }
}
