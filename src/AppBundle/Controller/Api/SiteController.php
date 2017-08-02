<?php
declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Service\SiteService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

class SiteController extends Controller
{
    /**
     * @Route("/site")
     * @Method("GET")
     */
    public function listAction(Request $request, SiteService $siteService)
    {
        $objs = ['result' => $siteService->findSitesAvailableForUser(
            $this->getUser(),
            $request->query->get('start'),
            $request->query->get('limit')
        )];
        $result = $this->get('jms_serializer')->serialize($objs, 'json', SerializationContext::create()
            ->setGroups(array('list'))
            ->setSerializeNull(true)
            ->enableMaxDepthChecks());

        return new Response($result);
    }

    /**
     * @Route("/site/{id}")
     * @Method("PUT")
     */
    public function updateAction($id, Request $request, SiteService $siteService)
    {
        $obj = ['result' => $siteService->update(
            $id,
            json_decode($request->getContent(), true),
            $this->getUser()
        )];
        $result = $this->get('jms_serializer')->serialize($obj, 'json', SerializationContext::create()
            ->setGroups(array('list'))
            ->setSerializeNull(true)
            ->enableMaxDepthChecks());

        return new Response($result);
    }
}