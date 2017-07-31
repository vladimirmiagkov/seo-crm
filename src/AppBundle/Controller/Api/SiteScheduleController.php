<?php
declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Service\SiteScheduleService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

class SiteScheduleController extends Controller
{
    /**
     * @Route("/siteschedule")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction()
    {
        $repository = $this->getRepository();
        $objects = ['result' => $repository->findAll()];
        $result = $this->get('jms_serializer')->serialize($objects, 'json', SerializationContext::create()->setGroups(array('list'))->setSerializeNull(true)->enableMaxDepthChecks());

        return new Response($result);
    }

    /**
     * @Route("/siteschedule/{id}")
     * @Method("PUT")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction($id, Request $request, SiteScheduleService $siteScheduleService)
    {
        $obj = ['result' => $siteScheduleService->update(
            $id,
            json_decode($request->getContent(), true),
            $this->getUser()
        )];
        $result = $this->get('jms_serializer')->serialize($obj, 'json', SerializationContext::create()->setGroups(array('list'))->setSerializeNull(true)->enableMaxDepthChecks());

        return new Response($result);
    }


    /**
     * @return \AppBundle\Repository\SiteScheduleRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:SiteSchedule');
        return $repository;
    }
}