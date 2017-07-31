<?php
declare(strict_types=1);

namespace DownloaderBundle\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverPoint;
use DownloaderBundle\Exception\DriverNotInitializedException;

class RemoteBrowserDownloaderService implements DownloaderInterface, RemoteBrowserDownloaderInterface
{
    /**
     * Timeout in ms: trying to connect to RemoteWebDriver host
     */
    const CONNECT_TIMEOUT = null;

    /**
     * Timeout in ms: trying to request from RemoteWebDriver host
     */
    const REQUEST_TIMEOUT = null;

    /**
     * @var RemoteWebDriver
     */
    protected $driver;


    public function __construct($remoteBrowserHost)
    {
        //$this->driver = RemoteWebDriver::create($remoteBrowserHost, DesiredCapabilities::chrome(), self::CONNECT_TIMEOUT, self::REQUEST_TIMEOUT);
        $this->driver = RemoteWebDriver::create($remoteBrowserHost, DesiredCapabilities::phantomjs(), self::CONNECT_TIMEOUT, self::REQUEST_TIMEOUT);
    }

    public function __destruct()
    {
        if ($this->isDriverInitialized()) {
            $this->driver->quit();
        }
    }

    public function request(string $url, string $method, array $options)
    {
        // TODO: Implement request() method.
    }

    /**
     * Load a new web page in the current browser window.
     *
     * @param string $uri
     * @return $this
     * @throws DriverNotInitializedException
     */
    public function loadUri($uri)
    {
        if (!$this->isDriverInitialized()) {
            throw new DriverNotInitializedException();
        }

        $this->driver->get($uri);

        return $this;
    }

    /**
     * Get the source of the last loaded page.
     *
     * @return string The current loaded page source.
     */
    public function getPageSource()
    {
        return $this->driver->getPageSource();
    }

    /**
     * Get <a> links from loaded page(JavaScript processed already).
     * Heavy long operation: retrieve 100 links take about 20-30 seconds long.
     *
     * @return array
     * @see https://github.com/facebook/php-webdriver/wiki/Example-command-reference
     */
    public function getPageLinks(): array
    {
        $result = [];
        $elements = $this->driver->findElements(WebDriverBy::tagName('a'));
        foreach ($elements as $k => $v) {
            $href = $v->getAttribute('href');
            if (!empty($href)) {
                $result[$k]['url'] = $href;
                $result[$k]['text'] = $v->getText();
                $result[$k]['rel'] = $v->getAttribute('rel');
                $result[$k]['title'] = $v->getAttribute('title');
                //$result[$k]['target'] = $v->getAttribute('target');
                //$result[$k]['isDisplayed'] = (int)$v->isDisplayed();
                //$result[$k]['x'] = $v->getLocation()->getX();
                //$result[$k]['y'] = $v->getLocation()->getY();
            }
        }

        return $result;
    }

    /**
     * Take a screenshot of the current loaded page.
     * Heavy long operation: 1 - 3 seconds long.
     *
     * @param string $saveToPath
     * @return string The screenshot in PNG format.
     */
    public function takeBrowserScreenshot($saveToPath)
    {
        return $this->driver->takeScreenshot($saveToPath);
    }

    /**
     * Set the size of the current window.
     *
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function setBrowserWindowSize($width, $height)
    {
        $this->driver->manage()->window()->setSize(new WebDriverDimension($width, $height));

        return $this;
    }

    /**
     * Refresh browser window
     */
    public function refreshBrowserWindow()
    {
        $this->driver->navigate()->refresh();
    }

    /**
     * @return bool
     */
    protected function isDriverInitialized()
    {
        return (bool)($this->driver instanceof RemoteWebDriver);
    }

    //public function getInfo()
    //{
    //    //echo "====The title is '" . $driver->getTitle() . "'<br>";
    //    //echo "====The current URI is '" . $driver->getCurrentURL() . "'<br>";
    //    //
    //    //$cookies = $driver->manage()->getCookies();
    //    //echo '<pre>';
    //    //print_r($cookies);
    //    //echo '</pre>';
    //}
}