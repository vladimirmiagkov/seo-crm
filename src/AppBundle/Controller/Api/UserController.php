<?php
declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use AppBundle\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

class UserController extends Controller
{
    /**
     * @Route("/user")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction()
    {
        $repository = $this->getRepository();
        $users = ['result' => $repository->findAll()];
        $result = $this->get('jms_serializer')->serialize($users, 'json', SerializationContext::create()
            ->setGroups(['list'])
            ->setSerializeNull(true)
            ->enableMaxDepthChecks());

        return new Response($result);
    }

    /**
     * @Route("/user")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request, UserService $userService)
    {
        $newUser = ['result' => $userService->createNewUser(
            json_decode($request->getContent(), true),
            $this->getUser()
        )];
        $result = $this->get('jms_serializer')->serialize($newUser, 'json', SerializationContext::create()
            ->setGroups(['list'])
            ->setSerializeNull(true)
            ->enableMaxDepthChecks());

        return new Response($result, 201); //201 Created: The request has been fulfilled, resulting in the creation of a new resource.
    }

    /**
     * @Route("/user/{id}")
     * @Method("PUT")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction($id, Request $request, UserService $userService)
    {
        $user = ['result' => $userService->updateUser(
            $id,
            json_decode($request->getContent(), true),
            $this->getUser()
        )];
        $result = $this->get('jms_serializer')->serialize($user, 'json', SerializationContext::create()
            ->setGroups(['list'])
            ->setSerializeNull(true)
            ->enableMaxDepthChecks());

        return new Response($result);
    }

    /**
     * @Route("/user/{id}")
     * @Method("DELETE")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function deleteAction($id, Request $request, UserService $userService)
    {
        $result = ['result' => $userService->deleteUser($id, $this->getUser())];

        return new Response(\json_encode($result), 204); //204 No Content: The server successfully processed the request and is not returning any content.
    }

    /**
     * @return \AppBundle\Repository\UserRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:User');
        return $repository;
    }
}