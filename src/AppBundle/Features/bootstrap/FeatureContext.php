<?php
declare(strict_types=1);

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
     * @Given there are following sites
     */
    public function thereAreFollowingSites(TableNode $table)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $metadata */
        $metadata = $this->em->getClassMetaData(Site::class);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator()); // For save our explicitly setted id.

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