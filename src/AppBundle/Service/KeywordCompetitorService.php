<?php
declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\KeywordCompetitor;
use AppBundle\Repository\KeywordCompetitorRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class KeywordCompetitorService
{
    /**
     * Maximum number of competitors, that we gonna save to db.
     * Business logic: we need only some first competitors.
     */
    const COMPETITORS_SAVE_LIMIT_MAX = 30;

    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var KeywordCompetitorRepository
     */
    protected $keywordCompetitorRepository;


    public function __construct(
        ValidatorInterface $validator,
        EntityManager $em,
        KeywordCompetitorRepository $keywordCompetitorRepository
    )
    {
        $this->validator = $validator;
        $this->em = $em;
        $this->keywordCompetitorRepository = $keywordCompetitorRepository;
    }


    /**
     * Add (save) new competitors to db.
     * Business logic: We add only few competitors from top,
     *          like from position 1 to position limit.
     *          This is optimization by db size.
     *
     * @param KeywordCompetitor[] $competitors
     *
     * @return null|array
     */
    public function saveCompetitorsToDb($competitors)
    {
        if (empty($competitors)) {
            return null;
        }

        foreach ($competitors as $competitor) {
            if ($competitor->getPosition() > self::COMPETITORS_SAVE_LIMIT_MAX) {
                break;
            }
            $this->em->persist($competitor);
        }
        $this->em->flush();

        return $competitors;
    }
}