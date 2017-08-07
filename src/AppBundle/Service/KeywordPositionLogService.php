<?php
declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\KeywordPosition;
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
     * Add record to db.
     *
     * @param $requests
     * @param $responses
     * @param $errors
     * @param $status
     * @param $keywordPosition
     * @return KeywordPositionLog
     */
    public function addNewKeywordPositionLogToDb($requests, $responses, $errors, $status, $keywordPosition)
    {
        $keywordPositionLog = new KeywordPositionLog();
        $keywordPositionLog->setRequests($requests);
        $keywordPositionLog->setResponses($responses);
        $keywordPositionLog->setErrors($errors);
        $keywordPositionLog->setStatus($status);
        if (null !== $keywordPosition) {
            if (!($keywordPosition instanceof KeywordPosition)) {
                throw new \RuntimeException('$keywordPosition MUST BE instanceof KeywordPosition');
            } else {
                $keywordPositionLog->setKeywordPosition($keywordPosition);
            }
        }
        $this->em->persist($keywordPositionLog);
        $this->em->flush();

        return $keywordPositionLog;
    }
}