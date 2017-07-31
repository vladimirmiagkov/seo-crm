<?php

namespace DownloaderBundle\Service;

interface DownloaderInterface
{
    public function request(string $url, string $method, array $options);
}