<?php
declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Site;
use AppBundle\Entity\User;
use AppBundle\Exception\SiteNotExistsException;
use AppBundle\Repository\SiteRepository;
use AppBundle\Security\Core\RsAcl;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SiteService
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
     * @var SiteRepository
     */
    protected $siteRepository;
    /**
     * @var RsAcl
     */
    protected $acl;


    public function __construct(
        ValidatorInterface $validator,
        EntityManager $em,
        RsAcl $acl
    )
    {
        $this->validator = $validator;
        $this->em = $em;
        $this->acl = $acl;

        $this->siteRepository = $em->getRepository('AppBundle:Site');
    }

    /**
     * Find sites available for user by acl.
     *
     * @param User     $user
     * @param null|int $start Pager
     * @param null|int $limit Pager
     * @return null|array
     */
    public function getSitesAvailableForUser(User $user, $start = null, $limit = null)
    {
        $alias = 'site';
        $qb = $this->em->createQueryBuilder()
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addSelect($alias)
            ->from('AppBundle\Entity\Site', $alias)
            ->andWhere($alias . '.deleted = false');

        if ($user->hasRole(User::ROLE_SUPER_ADMIN)) {
            // BUSINESS LOGIC: GRANT ACCESS FOR SUPER_ADMIN FOR ALL SITES.
        } else {
            $availableSitesIds = $this->acl->getIdsByClassName('AppBundle\Entity\Site', $user, RsAcl::VIEW);
            $qb->andWhere(
                $qb->expr()->in($alias . '.id', $availableSitesIds)
            );
        }

        $paginator = new Paginator($qb, true);
        $query = $qb->getQuery();
        $sites = $query->getResult();

        return [
            'totalRecords' => count($paginator),
            'sites'        => $sites,
        ];
    }

    /**
     * @param       $id
     * @param array $objData
     * @param User  $creator
     * @return Site
     * @throws SiteNotExistsException
     */
    public function update($id, array $objData, User $creator): Site
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Can not update SiteSchedule with empty id.');
        }

        /** @var Site $obj */
        $obj = $this->siteRepository->findOneBy(['id' => $id]);
        if (empty($obj)) {
            throw new SiteNotExistsException();
        }

        //TODO: ACL

        isset($objData['active']) ? $obj->setActive($objData['active']) : null;

        //isset($objData['intervalBetweenSiteDownload']) ? $obj->setIntervalBetweenSiteDownload($objData['intervalBetweenSiteDownload']) : null;
        //isset($objData['intervalBetweenPageDownload']) ? $obj->setIntervalBetweenPageDownload($objData['intervalBetweenPageDownload']) : null;
        //isset($objData['maxTimeLimitForSiteDownload']) ? $obj->setMaxTimeLimitForSiteDownload($objData['maxTimeLimitForSiteDownload']) : null;
        //isset($objData['maxDepthLevelLimitForSiteDownload']) ? $obj->setMaxDepthLevelLimitForSiteDownload($objData['maxDepthLevelLimitForSiteDownload']) : null;
        //isset($objData['useUserAgentFromRobotsTxt']) ? $obj->setUseUserAgentFromRobotsTxt($objData['useUserAgentFromRobotsTxt']) : null;
        //isset($objData['followNoFollowLinks']) ? $obj->setFollowNoFollowLinks($objData['followNoFollowLinks']) : null;
        //isset($objData['checkExternalLinksFor404']) ? $obj->setCheckExternalLinksFor404($objData['checkExternalLinksFor404']) : null;

        $obj->setModifiedBy($creator);
        $obj->setModifiedAtChange();

        $this->validate($obj);

        $this->em->persist($obj);
        $this->em->flush();

        return $obj;
    }

    /**
     * @param Site $obj
     * @throws \InvalidArgumentException
     */
    protected function validate(Site $obj)
    {
        $errors = $this->validator->validate($obj);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string)$errors);
        }
    }
}