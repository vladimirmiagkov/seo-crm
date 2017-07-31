<?php
declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Site;
use AppBundle\Security\Core\RsAcl;
use AppBundle\Service\SiteDataBlockService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

class SiteDataBlockController extends Controller
{
    /**
     * @Route("/sitedatablock/{id}")
     * @Method("GET")
     */
    public function listAction(Site $site, Request $request, RsAcl $acl, SiteDataBlockService $siteDataBlockService)
    {
        if (!$acl->isGranted(RsAcl::VIEW, $site, $this->getUser())) {
            throw $this->createAccessDeniedException();
        }

        $data = ['result' => $siteDataBlockService->getDataBlock(
            $site,
            $this->getUser(),
            $request->query->get('limit'),
            $request->query->get('offset'),
            $request->query->get('datefrom'),
            $request->query->get('dateto'),
            \json_decode(\urldecode($request->query->get('filter')), true)
        )];
        //$result = $this->get('jms_serializer')->serialize($data, 'json', SerializationContext::create()->setGroups(['datablock'])->setSerializeNull(true)->enableMaxDepthChecks());
        //$result = $this->get('jms_serializer')->serialize($data, 'json');
        $result = \json_encode($data);

        return new Response($result);
    }
}