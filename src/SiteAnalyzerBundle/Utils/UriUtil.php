<?php
declare(strict_types=1);

namespace SiteAnalyzerBundle\Utils;

use GuzzleHttp\Psr7\Uri;

class UriUtil
{
    /**
     * "https://www.example.com" return "example.com"
     * Warning: Uri without scheme, like "www.example.com" return "example.com"
     *
     * @param string $uri
     * @return null|string
     */
    public static function getHostFromUriWithoutWww(string $uri)
    {
        try {
            $result = null;
            $parsedUri = new Uri($uri);

            if (empty($parsedUri->getHost())) {
                $result = $uri;                  // "www.example.com"
            } else {
                $result = $parsedUri->getHost(); // "https://www.example.com"
            }

            if (substr(\strtolower($result), 0, 4) === 'www.') { // Remove 'www.'
                $result = substr($result, 4);
            }

            return $result;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get relative path from uri.
     * Like "/en/blog?id=1" from "http://www.example.com/en/blog?id=1"
     *
     * @param string $uri
     * @return null|string
     */
    public static function getRelativePathFromUri(string $uri)
    {
        //$uri = 'http://www.example.com#';
        try {
            $parsedUri = new Uri($uri);
            $result = '/'; // default root path
            if (!empty($parsedUri->getPath())) {
                $result = $parsedUri->getPath();
            }
            if (!empty($parsedUri->getQuery())) {
                $result .= '?' . $parsedUri->getQuery();
            }
            if (!empty($parsedUri->getFragment())) {
                $result .= '#' . $parsedUri->getFragment();
            }

            return $result;
        } catch (\Exception $e) {
            return null;
        }
    }
}