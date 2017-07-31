<?php
declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Keyword;
use AppBundle\Entity\SearchEngine;
use AppBundle\Repository\KeywordRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class KeywordService
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
     * @var KeywordRepository
     */
    protected $keywordRepository;


    public function __construct(
        ValidatorInterface $validator,
        EntityManager $em
    )
    {
        $this->validator = $validator;
        $this->em = $em;

        $this->keywordRepository = $em->getRepository('AppBundle:Keyword');
    }


    /**
     * Local 'Lock' mechanism for "check keyword position".
     * Reason: collecting keywords position from search engines may take too long time.
     *         So we need this against multiprocessing same keyword.
     * There no unlock mechanism. Just timeout.
     *
     * @param Keyword $keyword
     */
    public function lock(Keyword $keyword)
    {
        $keyword->setPositionLockedAt((new \DateTime()));
        $this->em->persist($keyword);
        $this->em->flush();
    }

    /**
     * @param Keyword $keyword
     */
    public function updatePositionLastCheck(Keyword $keyword)
    {
        $keyword->setPositionLastCheck((new \DateTime()));
        $this->em->persist($keyword);
        $this->em->flush();
    }

    ///**
    // * @param Keyword $obj
    // * @throws \InvalidArgumentException
    // */
    //protected function validate(Keyword $obj)
    //{
    //    $errors = $this->validator->validate($obj);
    //    if (count($errors) > 0) {
    //        throw new \InvalidArgumentException((string)$errors);
    //    }
    //}
}