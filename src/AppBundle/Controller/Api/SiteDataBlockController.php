<?php
declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Site;
use AppBundle\Helper\Filter\SiteDataBlockFilter;
use AppBundle\Security\Core\RsAcl;
use AppBundle\Service\SiteDataBlockService;
use AppBundle\Helper\DateTimeRange;
use AppBundle\Helper\Pager;
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

        $pager = new Pager($request->query->get('limit'), $request->query->get('offset'), 10, 0);

        $dateTimeRange = (new DateTimeRange(
            $request->query->get('datefrom'), // Higher: 150 3 377854
            $request->query->get('dateto'),   // Lower:  150 0 699454
            'now', 'now -1 month'
        ))
            ->makeRangeNegative()
            ->expandRangeToFullDay();

        $filter = new SiteDataBlockFilter();
        if (!empty($request->query->get('filter'))) {
            $filter->setFilterItemsFromArray(\json_decode(\urldecode($request->query->get('filter')), true));
        }

        $data = ['result' => $siteDataBlockService->getDataBlock($site, $pager, $dateTimeRange, $filter)];
        $result = \json_encode($data);

        return new Response($result);
    }
}