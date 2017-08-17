<?php
declare(strict_types=1);

use AppBundle\Entity\Site;
use AppBundle\Entity\SiteSchedule;
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
     * @Given there are following sites
     */
    public function thereAreFollowingSites(TableNode $table)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $metadata */
        $metadata = $this->em->getClassMetaData(Site::class);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator()); // For save our explicitly setted id.

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
        }
    }

    /**
     * @Given there are following site schedules
     */
    public function thereAreFollowingSiteSchedules(TableNode $table)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $metadata */
        $metadata = $this->em->getClassMetaData(SiteSchedule::class);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator()); // For save our explicitly setted id.

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
}