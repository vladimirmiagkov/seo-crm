<?php
declare(strict_types=1);

namespace SiteAnalyzerBundle\Site;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Predis\Client as RedisClient;
use SiteAnalyzerBundle\Site\ElasticSearch;
use SiteAnalyzerBundle\Resource\HtmlPage;
use SiteAnalyzerBundle\Site\DTO\SiteDownloadOptionsDTO;
use GuzzleHttp\Psr7\Uri;

class SiteAnalyzer
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var RedisClient
     */
    protected $redis;
    /**
     * @var ElasticSearch
     */
    protected $elasticSearch;

    public function __construct(
        ValidatorInterface $validator,
        RedisClient $redis,
        ElasticSearch $elasticSearch
    )
    {
        $this->validator = $validator;
        $this->redis = $redis;
        $this->elasticSearch = $elasticSearch;
    }

    /**
     * Analyze whole site.
     * TODO
     *
     * @param SiteDownloadOptionsDTO $options
     * @return bool
     */
    public function analyzeSite(SiteDownloadOptionsDTO $options): bool
    {
        $this->validateSiteDownloadOptionsDTO($options);
        $elasticSearchSiteId = $options->siteId . '_' . $options->siteStampName;

        $response = $this->elasticSearch->findPagesByIdenticalElement($elasticSearchSiteId, 'title');
        echo '<pre>', htmlspecialchars(print_r($response, true)), '</pre>';

        return true;
    }

    /**
     * @param SiteDownloadOptionsDTO $options
     * @throws \InvalidArgumentException
     */
    protected function validateSiteDownloadOptionsDTO(SiteDownloadOptionsDTO $options)
    {
        $errors = $this->validator->validate($options);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string)$errors);
        }
    }
}