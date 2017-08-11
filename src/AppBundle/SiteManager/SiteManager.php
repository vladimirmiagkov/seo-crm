<?php
declare(strict_types=1);

namespace AppBundle\SiteManager;

use AppBundle\Repository\SiteRepository;
use AppBundle\Security\Core\RsAcl;
use Doctrine\ORM\EntityManager;
use SiteAnalyzerBundle\Site\DTO\SiteDownloadOptionsDTO;
use SiteAnalyzerBundle\Site\SiteAnalyzer;
use SiteAnalyzerBundle\Site\SiteDownloader;
use Predis\Client as RedisClient;

class SiteManager
{
    const OPERATION_DOWNLOADING = 'downloading';
    const OPERATION_ANALYZING = 'analyzing';
    const DATABASE_DATETIME_FORMAT = 'Y-m-d H:i:s';
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
    /**
     * @var SiteDownloader
     */
    protected $siteDownloader;
    /**
     * @var SiteAnalyzer
     */
    protected $siteAnalyzer;
    /**
     * @var RedisClient
     */
    protected $redis;

    public function __construct(
        EntityManager $em,
        SiteRepository $siteRepository,
        RsAcl $acl,
        SiteDownloader $siteDownloader,
        SiteAnalyzer $siteAnalyzer,
        RedisClient $redis
    )
    {
        $this->em = $em;
        $this->siteRepository = $siteRepository;
        $this->acl = $acl;
        $this->siteDownloader = $siteDownloader;
        $this->siteAnalyzer = $siteAnalyzer;
        $this->redis = $redis;
    }

    /**
     * TODO: Is this work?
     */
    public function runCron()
    {
        $siteDownloadOptions = new SiteDownloadOptionsDTO();
        $siteDownloadOptions->siteId = 1;
        $siteDownloadOptions->siteHost = 'http://www.rsite.ru';
        $siteDownloadOptions->siteStampName = 'test';
        $siteDownloadOptions->excludePathsFromCrawling = ['/admin/*', '/aaaaa/'];


        if (0) {//todo: remove debug 
            $siteIdentificator = 'sitedwn:' . $siteDownloadOptions->siteId;
            //$siteDwnInfo = $this->redis->hgetall($siteIdentificator);
            //todo: is site in progress?
            //todo: resume download sites if they dwn process crashed
            $siteDwnInfo = $this->getSiteDownloadOptionsDTOAsArray($siteDownloadOptions);
            $siteDwnInfo['operation.currentStatus'] = self::OPERATION_DOWNLOADING;
            $siteDwnInfo['operation.startedAt'] = (new \DateTimeImmutable())->format(self::DATABASE_DATETIME_FORMAT);
            $siteDwnInfo['operation.lastPing'] = (new \DateTimeImmutable())->format(self::DATABASE_DATETIME_FORMAT);
            $siteDwnInfo['uri.currentInProcessing'] = '/';
            $siteDwnInfo['uri.totalProcessed'] = 0;
            $this->redis->hmset($siteIdentificator, $siteDwnInfo);

            //todo: make site screenshots

            //start site downloading process...
            $this->siteDownloader->downloadSite($siteDownloadOptions);

        } else {

            $this->siteAnalyzer->analyzeSite($siteDownloadOptions);
        }
    }

    protected function getSiteDownloadOptionsDTOAsArray(SiteDownloadOptionsDTO $options): array
    {
        $result = (array)$options;
        foreach ($result as $k => $v) {
            unset($result[$k]);
            $v = is_array($v) ? json_encode($v) : $v;
            $result['options.' . $k] = $v;
        }

        return $result;
    }

    //todo: cancelProceedSite
    //public function cancelProceedSite(int $siteId)
    //{
    //
    //}
}