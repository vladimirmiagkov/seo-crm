<?php
declare(strict_types=1);

use AppBundle\Entity\Keyword;
use AppBundle\Entity\KeywordPosition;
use AppBundle\Entity\Page;
use AppBundle\Entity\SearchEngine;
use AppBundle\Entity\Site;
use AppBundle\Entity\SiteSchedule;
use AppBundle\Entity\User;
use AppBundle\Security\Core\RsAcl;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\HttpKernel\KernelInterface;
use PHPUnit\Framework\Assert as Assertions;

require_once __DIR__ . '/../../../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

class FeatureContext implements Context
{
    /** @var KernelInterface */
    private $kernel;
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->em = $this->kernel->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @Given there are following search engines
     */
    public function thereAreFollowingSearchEngines(TableNode $table)
    {
        $this->setIdGeneratorToManualSet(SearchEngine::class);

        foreach ($table as $row) {
            $obj = new SearchEngine();
            $obj
                ->setId($row['id'])
                ->setActive((bool)$row['active'])
                ->setName($row['name'])
                ->setShortName($row['shortName'])
                ->setType((int)$row['type']);
            $this->em->persist($obj);
            $this->em->flush();
        }
    }

    /**
     * @Given there are following sites
     */
    public function thereAreFollowingSites(TableNode $table)
    {
        $this->setIdGeneratorToManualSet(Site::class);

        $rsAcl = $this->kernel->getContainer()->get('AppBundle\Security\Core\RsAcl');

        foreach ($table as $row) {
            $obj = new Site();
            $obj
                ->setId($row['id'])
                ->setActive((bool)$row['active'])
                ->setName($row['name'])
                ->setSeoStrategyKeywordPage((int)$row['seo_strategy_keyword_page'])
                ->setDeleted((bool)$row['deleted']);
            $this->em->persist($obj);
            $this->em->flush();

            // Set acl for site.
            if (!empty($row['acl(user_id,bitmask)'])) {
                $acl = explode(',', $row['acl(user_id,bitmask)']);
                if ($acl[1] > 0) {
                    /** @var User $user */
                    $user = $this->em->getRepository(User::class)->find($acl[0]);
                    if (!$user) {
                        throw new \Exception('Can not find user with id=' . $acl[0]);
                    }
                    $rsAcl->setAcl((int)$acl[1], $obj, $user);
                }
            }
        }
    }

    /**
     * @Given there are following pages
     */
    public function thereAreFollowingPages(TableNode $table)
    {
        $this->setIdGeneratorToManualSet(Page::class);

        foreach ($table as $row) {
            $obj = new Page();
            $obj
                ->setId($row['id'])
                ->setActive((bool)$row['active'])
                ->setName($row['name']);

            if (!empty($row['site_id'])) {
                /** @var Site $site */
                $site = $this->em->getRepository(Site::class)->find($row['site_id']);
                $obj->setSite($site);
            }

            if (!empty($row['searchengines_id'])) {
                $searchEnginesId = explode(',', $row['searchengines_id']);
                foreach ($searchEnginesId as $searchEngineId) {
                    /** @var SearchEngine $searchEngine */
                    $searchEngine = $this->em->getRepository(SearchEngine::class)->find($searchEngineId);
                    $obj->addSearchEngine($searchEngine);
                }
            }

            $this->em->persist($obj);
            $this->em->flush();
        }
    }

    /**
     * @Given there are following keywords
     */
    public function thereAreFollowingKeywords(TableNode $table)
    {
        $this->setIdGeneratorToManualSet(Keyword::class);

        foreach ($table as $row) {
            $obj = new Keyword();
            $obj
                ->setId($row['id'])
                ->setActive((bool)$row['active'])
                ->setName($row['name']);

            if (!empty($row['from_place'])) {
                $obj->setFromPlace($row['from_place']);
            }

            if (!empty($row['search_engine_request_limit'])) {
                $obj->setSearchEngineRequestLimit((int)$row['search_engine_request_limit']);
            }

            if (!empty($row['site_id'])) {
                /** @var Site $site */
                $site = $this->em->getRepository(Site::class)->find($row['site_id']);
                $obj->setSite($site);
            }

            if (!empty($row['pages_id'])) {
                $pagesId = explode(',', $row['pages_id']);
                foreach ($pagesId as $pageId) {
                    /** @var Page $page */
                    $page = $this->em->getRepository(Page::class)->find($pageId);
                    $obj->addPage($page);
                }
            }

            if (!empty($row['searchengines_id'])) {
                $searchEnginesId = explode(',', $row['searchengines_id']);
                foreach ($searchEnginesId as $searchEngineId) {
                    /** @var SearchEngine $searchEngine */
                    $searchEngine = $this->em->getRepository(SearchEngine::class)->find($searchEngineId);
                    $obj->addSearchEngine($searchEngine);
                }
            }

            $this->em->persist($obj);
            $this->em->flush();
        }
    }

    /**
     * @Given there are following keywords positions
     */
    public function thereAreFollowingKeywordsPositions(TableNode $table)
    {
        $this->setIdGeneratorToManualSet(KeywordPosition::class);

        foreach ($table as $row) {
            $obj = new KeywordPosition();
            $obj
                ->setId($row['id'])
                ->setPosition((int)$row['position'])
                ->setUrl($row['url'])
                ->setCreatedAt(new \DateTime($row['created']));

            if (!empty($row['keyword_id'])) {
                /** @var Keyword $keyword */
                $keyword = $this->em->getRepository(Keyword::class)->find($row['keyword_id']);
                $obj->setKeyword($keyword);
            }

            if (!empty($row['searchengine_id'])) {
                /** @var SearchEngine $searchEngine */
                $searchEngine = $this->em->getRepository(SearchEngine::class)->find($row['searchengine_id']);
                $obj->setSearchEngine($searchEngine);
            }

            $this->em->persist($obj);
            $this->em->flush();
        }
    }

    /**
     * @Given there are following site schedules
     */
    public function thereAreFollowingSiteSchedules(TableNode $table)
    {
        $this->setIdGeneratorToManualSet(SiteSchedule::class);

        foreach ($table as $row) {
            /** @var Site $site */
            $site = $this->em->getRepository(Site::class)->find($row['site_id']);
            $obj = new SiteSchedule();
            $obj
                ->setId($row['id'])
                ->setActive((bool)$row['active'])
                ->setSite($site)
                ->setIntervalBetweenSiteDownload($row['interval_between_site_download']);
            $this->em->persist($obj);
            $this->em->flush();
        }
    }

    /**
     * It's for set and save our explicitly (manually) setted id.
     *
     * @param string $class
     */
    private function setIdGeneratorToManualSet(string $class)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $metadata */
        $metadata = $this->em->getClassMetaData($class);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    }
}