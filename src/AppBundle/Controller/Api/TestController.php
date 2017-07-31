<?php
declare(strict_types=1);

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

class TestController extends Controller
{
    /**
     * Special route: Test admin login.
     * WARNING: THIS METHOD ONLY FOR TESTs.
     *
     * @Route("/test/loggedinadmin")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function testLoggedInAdminAction()
    {
        return new Response('admin successfully logged in.');
    }

    /**
     * Special route: Test client login.
     * WARNING: THIS METHOD ONLY FOR TESTs.
     *
     * @Route("/test/loggedinclient")
     * @Method("GET")
     * @Security("has_role('ROLE_CLIENT')")
     */
    public function testLoggedInClientAction()
    {
        return new Response('client successfully logged in.');
    }
}