<?php
declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\SiteSchedule;
use AppBundle\Entity\User;
use AppBundle\Exception\SiteScheduleNotExistsException;
use AppBundle\Repository\SiteScheduleRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SiteScheduleService
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var SiteScheduleRepository
     */
    protected $siteScheduleRepository;


    public function __construct(
        ValidatorInterface $validator,
        EntityManager $em
    )
    {
        $this->validator = $validator;
        $this->em = $em;

        $this->siteScheduleRepository = $em->getRepository('AppBundle:SiteSchedule');
    }

    /**
     * @param       $id
     * @param array $objData
     * @param User  $creator
     * @return SiteSchedule
     * @throws SiteScheduleNotExistsException
     */
    public function update($id, array $objData, User $creator): SiteSchedule
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Can`t update SiteSchedule with empty id.');
        }

        /** @var SiteSchedule $obj */
        $obj = $this->siteScheduleRepository->findOneBy(['id' => $id]);
        if (empty($obj)) {
            throw new SiteScheduleNotExistsException();
        }

        isset($objData['active']) ? $obj->setActive($objData['active']) : null;

        isset($objData['intervalBetweenSiteDownload']) ? $obj->setIntervalBetweenSiteDownload($objData['intervalBetweenSiteDownload']) : null;
        isset($objData['intervalBetweenPageDownload']) ? $obj->setIntervalBetweenPageDownload($objData['intervalBetweenPageDownload']) : null;
        isset($objData['maxTimeLimitForSiteDownload']) ? $obj->setMaxTimeLimitForSiteDownload($objData['maxTimeLimitForSiteDownload']) : null;
        isset($objData['maxDepthLevelLimitForSiteDownload']) ? $obj->setMaxDepthLevelLimitForSiteDownload($objData['maxDepthLevelLimitForSiteDownload']) : null;
        isset($objData['useUserAgentFromRobotsTxt']) ? $obj->setUseUserAgentFromRobotsTxt($objData['useUserAgentFromRobotsTxt']) : null;
        isset($objData['followNoFollowLinks']) ? $obj->setFollowNoFollowLinks($objData['followNoFollowLinks']) : null;
        isset($objData['checkExternalLinksFor404']) ? $obj->setCheckExternalLinksFor404($objData['checkExternalLinksFor404']) : null;

        $obj->setModifiedBy($creator);
        $obj->setModifiedAtChange();

        $this->validate($obj);

        $this->em->persist($obj);
        $this->em->flush();

        return $obj;
    }

    /**
     * @param SiteSchedule $obj
     * @throws \InvalidArgumentException
     */
    protected function validate(SiteSchedule $obj)
    {
        $errors = $this->validator->validate($obj);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string)$errors);
        }
    }
}