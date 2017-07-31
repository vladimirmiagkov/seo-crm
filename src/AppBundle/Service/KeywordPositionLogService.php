<?php
declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\KeywordPositionLog;
use AppBundle\Repository\KeywordPositionLogRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class KeywordPositionLogService
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
     * @var KeywordPositionLogRepository
     */
    protected $keywordPositionLogRepository;


    public function __construct(
        ValidatorInterface $validator,
        EntityManager $em
    )
    {
        $this->validator = $validator;
        $this->em = $em;

        $this->keywordPositionLogRepository = $em->getRepository('AppBundle:KeywordPositionLog');
    }

    /**
     * Create new instance of KeywordPositionLog
     * TODO: hm...
     *
     * @return KeywordPositionLog
     */
    public function createNewLogger()
    {
        return new KeywordPositionLog();
    }

    /**
     * Add / save record to db.
     *
     * @param KeywordPositionLog $keywordPositionLog
     * @return KeywordPositionLog
     */
    public function save(KeywordPositionLog $keywordPositionLog)
    {
        $this->em->persist($keywordPositionLog);
        $this->em->flush();

        return $keywordPositionLog;
    }
}