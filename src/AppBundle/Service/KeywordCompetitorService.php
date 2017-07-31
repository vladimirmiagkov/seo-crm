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
        EntityManager $em
    )
    {
        $this->validator = $validator;
        $this->em = $em;

        $this->keywordCompetitorRepository = $em->getRepository('AppBundle:KeywordCompetitor');
    }


    /**
     * Add (save) new competitors to db.
     * WARNING: We add only few competitors from top,
     *          like from position 1 to position self::COMPETITORS_SAVE_LIMIT_MAX.
     *          This is optimization by db size.
     *
     * @param KeywordCompetitor[] $competitors
     *
     * @return null|array
     */
    public function saveNewCompetitors($competitors)
    {
        if (empty($competitors)) {
            return null;
        }

        foreach ($competitors as $competitor) {
            if ($competitor->getPosition() > self::COMPETITORS_SAVE_LIMIT_MAX) {
                continue; // We add only few competitors from top.
            }

            $this->em->persist($competitor);
        }

        $this->em->flush();

        return $competitors;
    }
}