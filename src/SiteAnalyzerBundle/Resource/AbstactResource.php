<?php
declare(strict_types=1);

namespace SiteAnalyzerBundle\Resource;

abstract class AbstactResource
{
    /**
     * Fully qualified resource url, like "http://www.example.com/123.html"
     *
     * @var string
     */
    protected $url;
    /**
     * Response code from server headers.
     *
     * @var int
     */
    protected $responseCode;
    /**
     * Array of redirects. Only if uri was redirected.
     *
     * @var array
     */
    protected $redirects = [];
    /**
     * Response headers from server.
     *
     * @var array
     */
    protected $headers = [];
    /**
     * Content identification checksum.
     * Use case: compare two checksum = content was changed?
     *
     * @var string
     */
    protected $contentChecksum;
    /**
     * Total size in bytes.
     *
     * @var int
     */
    protected $sizeTotal;
    /**
     * Total downloading time with connection time. Milliseconds.
     *
     * @var int
     */
    protected $downloadTime;
    /**
     * When resource was initially downloaded. Unix timestamp.
     *
     * @var int
     */
    protected $downloadedAt;


    /**
     * Get fully qualified resource url, like "http://www.example.com/123.html"
     *
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set fully qualified resource url, like "http://www.example.com/123.html"
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = mb_strtolower(trim((string)$url));
        return $this;
    }

    /**
     * Get response code from server headers.
     *
     * @return null|int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set response code from server headers.
     *
     * @param int $responseCode
     * @return $this
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = (int)$responseCode;
        return $this;
    }

    /**
     * Get array of redirects. Only if uri was redirected.
     * Format: toUrl => statusCode
     *
     * @return array
     */
    public function getRedirects(): array
    {
        return $this->redirects;
    }

    /**
     * Add redirects to array. Only if uri was redirected.
     *
     * @param string $toUrl
     * @param int    $statusCode
     * @return $this
     */
    public function addRedirect($toUrl, $statusCode)
    {
        $this->redirects[mb_strtolower(trim((string)$toUrl))] = (int)$statusCode;
        return $this;
    }

    /**
     * Get response headers from server.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set response headers from server.
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Get content identification checksum.
     *
     * @return null|string
     */
    public function getContentChecksum()
    {
        return $this->contentChecksum;
    }

    /**
     * Set content identification checksum.
     *
     * @param string $contentChecksum
     * @return $this
     */
    public function setContentChecksum($contentChecksum)
    {
        $this->contentChecksum = trim((string)$contentChecksum);
        return $this;
    }

    /**
     * Get resource total size. Bytes.
     *
     * @return null|int
     */
    public function getSizeTotal()
    {
        return $this->sizeTotal;
    }

    /**
     * Set resource total size. Bytes.
     *
     * @param int $sizeTotal
     * @return $this
     */
    public function setSizeTotal($sizeTotal)
    {
        $this->sizeTotal = (int)$sizeTotal;
        return $this;
    }

    /**
     * Get total downloading time with connection time. Milliseconds.
     *
     * @return null|int
     */
    public function getDownloadTime()
    {
        return $this->downloadTime;
    }

    /**
     * Set total downloading time with connection time. Milliseconds.
     *
     * @param int $downloadTime
     * @return $this
     */
    public function setDownloadTime($downloadTime)
    {
        $this->downloadTime = (int)$downloadTime;
        return $this;
    }

    /**
     * Get when resource was initially downloaded. Unix timestamp.
     *
     * @return null|int
     */
    public function getDownloadedAt()
    {
        return $this->downloadedAt;
    }

    /**
     * Set when resource was initially downloaded. Unix timestamp.
     *
     * @param int $downloadedAt
     * @return $this
     */
    public function setDownloadedAt(int $downloadedAt)
    {
        $this->downloadedAt = $downloadedAt;
        return $this;
    }
}