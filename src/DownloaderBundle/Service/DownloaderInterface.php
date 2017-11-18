<?php

namespace DownloaderBundle\Service;

interface DownloaderInterface
{
    /**
     * Main "request to download" source code for provided url.
     *
     * @param string $url
     * @param string $method
     * @param array  $options
     * @return mixed
     */
    public function request(string $url, string $method, array $options);
}