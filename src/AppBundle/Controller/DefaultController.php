<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Site;
use AppBundle\Entity\Page;
use AppBundle\Entity\Keyword;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Method("GET")
     * @Template("default/homepage.twig")
     * //@Security("is_authenticated()")
     */
    public function indexAction(Request $request)
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'access only for role: ROLE_ADMIN');

        //return $this->render('default/index.html.twig', [
        //    'base_dir' => realpath($this->container->getParameter('kernel.root_dir') . '/..'),
        //]);
        return ['rscontent' => 'path: / <br><a href="/app_dev.php/init">/init</a>'];
    }

    /**
     * @Route("/init")
     * @Method("GET")
     */
    public function initAction()
    {
        $sendToTemplate = [];

        echo '123';

        //$this->get('rs.site_manager')->runCron();

        //$em = $this->getDoctrine()->getManager();
        //$repositoryUser = $em->getRepository('AppBundle:User');

        //Checking Access
        //if (false === $this->isGranted('EDIT', $elementTreeSite)) {
        //    throw new AccessDeniedException();
        //}

        //return $this->render('default/index.html.twig', ['base_dir' => realpath($this->container->getParameter('kernel.root_dir') . '/..'),]);
        return $this->render('default/test.twig', ['rscontent' => implode("\n", $sendToTemplate)]);
    }
}