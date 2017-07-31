<?php
declare(strict_types=1);

namespace AppBundle\Security\Core;

use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Util\ClassUtils;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;

/**
 * Custom ACL
 */
class RsAcl
{
    const VIEW = 1;           // 1 << 0 //просматривать / да
    const CREATE = 2;         // 1 << 1 //добавлять(add child)
    const EDIT_OWN = 4;       // 1 << 2 //ред./уд. свои
    const EDIT_OTHER = 8;     // 1 << 3 //ред./уд. чужие
    //const UNDELETE = 16;      // 1 << 4 //
    //const OPERATOR = 32;      // 1 << 5 //
    //const MASTER = 64;        // 1 << 6 //
    //const OWNER = 128;        // 1 << 7 //

    const VIEW_COMMENTS = 256;                 // 1 << 8 //просматривать комментарии(отображать комментарии?)
    const EDIT_COMMENTS = 512;                 // 1 << 9 //CRUD комментарии(CUD только свои комментарии)(full CRUD for ROLE_ADMIN)
    const AVAILABLE_SITE_CNT = 1024;           // 1 << 10 //доступен детальный анализ счётч посещ
    const AVAILABLE_SITE_DETAIL = 2048;        // 1 << 11 //доступен детальный анализ сайта
    const AVAILABLE_PAGE_DETAIL = 4096;        // 1 << 12 //доступен детальный анализ страницы
    const AVAILABLE_COMPETITOR_DETAIL = 8192;  // 1 << 13 //доступен детальный анализ конкурентов
    //const  = 16384;            // 1 << 14
    //const  = 32768;            // 1 << 15
    //const  = 65536;            // 1 << 16
    //const  = 131072;           // 1 << 17
    //const  = 262144;           // 1 << 18
    //const  = 524288;           // 1 << 19
    //const  = 1048576;          // 1 << 20
    //const  = 2097152;          // 1 << 21
    //const  = 4194304;          // 1 << 22
    //const  = 8388608;          // 1 << 23
    //const  = 16777216;         // 1 << 24
    //const  = 33554432;         // 1 << 25
    //const  = 67108864;         // 1 << 26
    //const  = 134217728;        // 1 << 27
    //const  = 268435456;        // 1 << 28
    //const  = 536870912;        // 1 << 29

    const MASK_IDDQD = 1073741823; // 1 << 0 | 1 << 1 | ... | 1 << 30 // Gives all ACL permissions

    /*$mask = (new MaskBuilder())
            ->add(MaskBuilder::MASK_VIEW)
            ->add(MaskBuilder::MASK_EDIT)
            ->add(MaskBuilder::MASK_CREATE)
            ->add(MaskBuilder::MASK_DELETE)
            ->add(MaskBuilder::MASK_UNDELETE)
            ->add(MaskBuilder::MASK_OPERATOR)
            ->add(MaskBuilder::MASK_MASTER)
            ->add(MaskBuilder::MASK_OWNER)
            ->get();*/

    /**
     * @var MutableAclProviderInterface
     */
    protected $aclProvider;
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(AclProviderInterface $aclProvider, EntityManager $em)
    {
        $this->aclProvider = $aclProvider;
        $this->em = $em;
    }


    protected function getObjectIdentity($object)
    {
        return ObjectIdentity::fromDomainObject($object);
    }

    protected function getSecurityIdentity(User $user)
    {
        //return UserSecurityIdentity::fromAccount($user);
        return new UserSecurityIdentity($user->getId(), ClassUtils::getRealClass($user));
    }

    protected function getMaskProcessed(int $mask)
    {
        //$builder = new MaskBuilder;
        //$builder->add($mask);
        //$mask = $builder->get();
        return $mask;
    }

    /**
     * Check access
     *
     * @param int   $mask
     * @param mixed $object
     * @param User  $user
     * @return bool
     */
    public function isGranted($mask, $object, User $user)
    {
        if ($user->hasRole(User::ROLE_SUPER_ADMIN)) { // Hack: Grant access for SUPER_ADMIN
            return true;
        }

        $objectIdentity = $this->getObjectIdentity($object);
        $securityIdentity = $this->getSecurityIdentity($user);
        $mask = $this->getMaskProcessed($mask);

        try {
            $acl = $this->aclProvider->findAcl($objectIdentity, array($securityIdentity));
        } catch (AclNotFoundException $e) {
            return false;
        }

        try {
            return $acl->isGranted(array($mask), array($securityIdentity), false);
        } catch (NoAceFoundException $e) {
            return false;
        }
    }

    /**
     * Set / update ACL
     *
     * @param int   $mask
     * @param mixed $object
     * @param User  $user
     * @throws \RuntimeException
     */
    public function setAcl($mask, $object, User $user)
    {
        $objectIdentity = $this->getObjectIdentity($object);
        $securityIdentity = $this->getSecurityIdentity($user);
        $mask = $this->getMaskProcessed($mask);

        try {
            /** @var \Symfony\Component\Security\Acl\Domain\Acl $acl */
            $acl = $this->aclProvider->findAcl($objectIdentity, array($securityIdentity));
            // Acl already exist
            //$this->aclProvider->deleteAcl($objectIdentity);
            try { // Update user access
                $objectAces = $acl->getObjectAces();
                foreach ($objectAces as $i => $ace) {
                    $acl->updateObjectAce($i, $mask);
                }
            } catch (\Exception $e) {
                throw new \RuntimeException('Cant update acl user access');
            }
        } catch (AclNotFoundException $e) { // Acl not exist
            $acl = $this->aclProvider->createAcl($objectIdentity);
            $acl->insertObjectAce($securityIdentity, $mask);
        }
        $this->aclProvider->updateAcl($acl);
    }

    /**
     * Get objects ID's under ACL control for user, by class name and mask.
     *
     * @param string $className Full class name like 'AppBundle\Entity\Site'
     * @param User   $user
     * @param int    $mask      ACL Rights mask
     * @return array
     */
    public function getIdsByClassName(string $className, User $user, int $mask = 0)
    {
        $result = [];

        $conn = $this->em->getConnection();
        $sql = '
            SELECT o.object_identifier
            FROM acl_classes c
            INNER JOIN acl_entries e ON e.class_id = c.id
            INNER JOIN acl_object_identities o ON o.id = e.object_identity_id
            INNER JOIN acl_security_identities u ON u.id = e.security_identity_id
            WHERE
              c.class_type = :class_type
              AND u.username = :username
              AND (e.mask & :mask) = :mask
        ';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('class_type', $className);
        $stmt->bindValue('username', $user->getId());
        $stmt->bindValue('mask', $mask);
        $stmt->execute();
        $request = $stmt->fetchAll();
        foreach ($request as $v) {
            $result[] = $v['object_identifier'];
        }

        return $result;
    }
}