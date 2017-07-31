<?php
declare(strict_types=1);

namespace SiteAnalyzerBundle\Resource;

use SiteAnalyzerBundle\Resource\Dom\Link;
use SiteAnalyzerBundle\Utils\StringUtil;
use Symfony\Component\DomCrawler\Crawler as SymfonyDomCrawler;

/**
 * Html page with valuable information for SEO.
 */
class HtmlPage extends AbstactResource
{
    // HTML HEAD ------------------------------------------------------------------------------------------------------
    /**
     * Raw page source code.
     *
     * @var string
     */
    protected $html;
    /**
     * HTML 4.01: <meta http-equiv="content-type" content="text/html; charset=UTF-8">
     *            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
     * HTML5: <meta charset="UTF-8">
     *
     * @var string in lowercase, like "utf-8"
     */
    protected $charset;
    /**
     * <html lang="en"
     *
     * @var string
     */
    protected $htmlLang;
    /**
     * Site base uri.
     * <base href="http://www.a.com/" />
     *
     * @var string
     */
    protected $baseUri;
    /**
     * This commonly used title.
     * <title>...</title>
     *
     * @var string
     */
    protected $title;
    /**
     * This used only for seo engines. Take a look at youtube source code.
     * <meta name="title" content="...">
     *
     * @var string
     */
    protected $titleForSeo;
    /**
     * <meta name="keywords" content="...">
     *
     * @var string
     */
    protected $keywords;
    /**
     * <meta name="description" content="...">
     *
     * @var string
     */
    protected $description;
    /**
     * <link rel="canonical" href="...">
     *
     * @var string
     */
    protected $canonical;
    /**
     * <link rel="shortcut icon" href="/favicon.ico" type="image/vnd.microsoft.icon" />
     * <link rel="shortcut icon" href="https://a.com/yts/img/favicon-vflz7uhzw.ico" type="image/x-icon">
     *
     * @var string
     */
    protected $favicon;
    /**
     * <link rel="shortlink" href="http://www.a.com/123.html" />
     *
     * @var string
     */
    protected $shortlink;

    //todo: https://en.wikipedia.org/wiki/Noindex
    //<meta name="robots" content="noindex">
    //<meta name="robots" content="noindex, follow">
    //<meta name="googlebot" content="noindex">

    //todo: excluded from robots.txt ?

    //todo: microformats //class="robots-noindex" //<div class="robots-noindex robots-follow">Text.</div>

    // HTML BODY ------------------------------------------------------------------------------------------------------
    /**
     * Html body without html tags.
     * <body>...</body>
     *
     * @var string
     */
    protected $bodyText;
    /**
     * Html body identification checksum.
     * Use case: compare two checksum = content was changed?
     *
     * @var string
     */
    protected $bodyChecksum;
    /**
     * Links. Outgoing.
     *
     * @var Link[]
     */
    protected $links = [];
    /**
     * Tags.
     *
     * @var array
     */
    protected $h1 = [];
    protected $h2 = [];
    protected $h3 = [];
    protected $h4 = [];
    protected $h5 = [];
    protected $h6 = [];
    protected $p = [];
    /**
     * Images.
     *
     * @var array
     */
    protected $images = [];


    public function __construct()
    {
    }

    /**
     * Parse html page into valuable pieces and set to current object.
     *
     * @param string $html
     * @param string $currentUri
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAndParseHtml(string $html, string $currentUri)
    {
        $html = trim((string)$html);
        if (!self::isValidHtml($html)) {
            throw new \InvalidArgumentException('Invalid html code');
        }

        $crawler = new SymfonyDomCrawler($html, $currentUri);
        $crawlerLowerCase = new SymfonyDomCrawler(mb_strtolower($html), $currentUri);

        $this->setUrl($currentUri);
        $this->setHtml($html);// todo: do we need this?

        try {
            // Charset For HTML 4
            $this->setCharset(self::parseStringForCharset($crawlerLowerCase->filter('head > meta[http-equiv="content-type"]')->last()->attr('content')));
        } catch (\Exception $e) {
        }
        try {
            // Charset For HTML 5 //High priority
            $this->setCharset($crawlerLowerCase->filter('head > meta[charset]')->last()->attr('charset'));
        } catch (\Exception $e) {
        }

        try {
            $this->setHtmlLang($crawlerLowerCase->filter('html')->attr('lang'));
        } catch (\Exception $e) {
        }

        try {
            $this->setBaseUri($crawlerLowerCase->filter('head > base')->last()->attr('href'));
        } catch (\Exception $e) {
        }

        try {
            $this->setTitle($crawler->filter('head > title')->last()->text());
        } catch (\Exception $e) {
        }
        try {
            $this->setTitleForSeo($crawler->filter('head > meta[name="title"]')->last()->attr('content'));
        } catch (\Exception $e) {
        }

        try {
            $this->setKeywords($crawler->filter('head > meta[name="keywords"]')->last()->attr('content'));
        } catch (\Exception $e) {
        }

        try {
            $this->setDescription($crawler->filter('head > meta[name="description"]')->last()->attr('content'));
        } catch (\Exception $e) {
        }

        try {
            $this->setCanonical($crawlerLowerCase->filter('head > link[rel="canonical"]')->last()->attr('href'));
        } catch (\Exception $e) {
        }

        try {
            $this->setFavicon($crawlerLowerCase->filter('head > link[rel^="shortcut"]')->last()->attr('href'));
        } catch (\Exception $e) {
        }

        try {
            $this->setShortlink($crawlerLowerCase->filter('head > link[rel="shortlink"]')->last()->attr('href'));
        } catch (\Exception $e) {
        }

        // Html body text
        try {
            //if ($currentUri === '') {
            //    $a = 1; // Use for debug breakpoint.
            //}
            // A bug in $crawler: 
            // <script type="text/javascript">(function($, undefined){
            //        $('.aaa').prepend('<div class="bbb"></div>');
            // })(jQuery);</script>
            //
            // Auto-converted to:
            // <script type="text/javascript">(function($, undefined){
            //        $('.aaa').prepend('<div class="bbb"></div></script>');
            // })(jQuery);
            //
            // So, we need to (-report- to lazy) handle it by ourselves:
            $crawlerForBody = new SymfonyDomCrawler(StringUtil::clearSpecialHtmlTags($html), $currentUri);

            $this->setBodyText($crawlerForBody->filter('body')->html());
            $this->setBodyChecksum($this->getBodyText());
            unset($crawlerForBody);
        } catch (\Exception $e) {
        }

        // Search for links
        $crawler->filter('a')->each(function ($node) use ($currentUri) {
            /** @var $node \Symfony\Component\DomCrawler\Crawler */
            $href = $node->link()->getNode()->getAttribute('href');
            if ($href !== '') { // dont add <a href="">...</a>
                $this->addLink($this->createLinkFromArray([
                    'url'          => $node->link()->getUri(),
                    'hrefOriginal' => $href,
                    'text'         => $node->text(),
                    'title'        => $node->attr('title'),
                    'rel'          => $node->attr('rel'),
                ], $currentUri));
            }
        });


        return $this;
    }

    /**
     * Is valid html code?
     *
     * @param string $html
     * @return bool
     */
    public static function isValidHtml($html): bool
    {
        $html = trim((string)$html);
        if (empty($html)) {
            return false;
        }
        if (false === mb_strpos($html, 'html')) {
            return false;
        }

        return true;
    }

    /**
     * Search in string 'text/html; charset=utf-8' 'charset=' value
     *
     * @param $str
     * @return string
     */
    public static function parseStringForCharset(string $str)
    {
        $result = trim($str);
        $result .= !empty($result) ? ';' : ''; // add terminate delimiter 'text/html; charset=utf-8' . ';' for nice parsing
        preg_match("/charset=(.*?;)/", $result, $result);
        if (isset($result[1])) {
            $result = trim((string)str_replace(['"', '\'', ';'], '', $result[1]));
        } else {
            $result = null;
        }
        return $result;
    }


    // GETTERS / SETTERS ----------------------------------------------------------------------------------------------

    /**
     * Get raw page source code.
     *
     * @return null|string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set raw page source code.
     *
     * @param string $html
     * @return $this
     */
    public function setHtml($html)
    {
        $this->html = trim((string)$html);
        return $this;
    }

    /**
     * Get page charset.
     *
     * @return null|string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Set page charset.
     *
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = mb_strtolower(trim((string)$charset));
        return $this;
    }

    /**
     * Get html language.
     * Was parsed from: <html lang="en" ...
     *
     * @return null|string
     */
    public function getHtmlLang()
    {
        return $this->htmlLang;
    }

    /**
     * Set html language.
     *
     * @param string $htmlLang
     * @return $this
     */
    public function setHtmlLang($htmlLang)
    {
        $this->htmlLang = mb_strtolower(trim((string)$htmlLang));
        return $this;
    }

    /**
     * Get site base uri.
     * Was parsed from: <base href="http://www.a.com/" />
     *
     * @return null|string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * Set site base uri.
     *
     * @param string $baseUri
     * @return $this
     */
    public function setBaseUri($baseUri)
    {
        $baseUri = mb_strtolower(trim((string)$baseUri));
        $this->baseUri = empty($baseUri) ? null : $baseUri;
        return $this;
    }

    /**
     * Get page title.
     * Was parsed from: <title>...</title>
     *
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get page title.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        //$title = (bool)random_int(0, 1) ? mb_strtolower($title) : mb_strtoupper($title);
        $this->title = trim((string)$title);
        return $this;
    }

    /**
     * Get page SEO title. This used only for seo engines.
     * Was parsed from: <meta name="title" content="...">
     *
     * @return null|string
     */
    public function getTitleForSeo()
    {
        return $this->titleForSeo;
    }

    /**
     * Set page SEO title. This used only for seo engines.
     *
     * @param string $titleForSeo
     * @return $this
     */
    public function setTitleForSeo($titleForSeo)
    {
        $this->titleForSeo = trim((string)$titleForSeo);
        return $this;
    }

    /**
     * Get page keywords.
     * Was parsed from: <meta name="keywords" content="...">
     *
     * @return null|string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set page keywords.
     *
     * @param string $keywords
     * @return $this
     */
    public function setKeywords($keywords)
    {
        $this->keywords = trim((string)$keywords);
        return $this;
    }

    /**
     * Get page description.
     * Was parsed from: <meta name="description" content="...">
     *
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set page description.
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = trim((string)$description);
        return $this;
    }

    /**
     * Get page canonical.
     * Was parsed from: <link rel="canonical" href="...">
     *
     * @return null|string
     */
    public function getCanonical()
    {
        return $this->canonical;
    }

    /**
     * Set page canonical.
     *
     * @param string $canonical
     * @return $this
     */
    public function setCanonical($canonical)
    {
        $this->canonical = mb_strtolower(trim((string)$canonical));
        return $this;
    }

    /**
     * Get page favicon.
     * Was parsed from: <link rel="shortcut icon" href="https://a.com/favicon.ico" type="image/x-icon">
     *
     * @return null|string
     */
    public function getFavicon()
    {
        return $this->favicon;
    }

    /**
     * Set page favicon.
     *
     * @param string $favicon
     * @return $this
     */
    public function setFavicon($favicon)
    {
        $this->favicon = mb_strtolower(trim((string)$favicon));
        return $this;
    }

    /**
     * Get page shortlink.
     * Was parsed from: <link rel="shortlink" href="http://www.a.com/123.html" />
     *
     * @return null|string
     */
    public function getShortlink()
    {
        return $this->shortlink;
    }

    /**
     * Set page shortlink.
     *
     * @param string $shortlink
     * @return $this
     */
    public function setShortlink($shortlink)
    {
        $this->shortlink = mb_strtolower(trim((string)$shortlink));
        return $this;
    }

    /**
     * Get Html body without html tags.
     * <body>...</body>
     *
     * @return null|string
     */
    public function getBodyText()
    {
        return $this->bodyText;
    }

    /**
     * Set Html body without html tags.
     * <body>...</body>
     *
     * @param string $bodyText
     * @return $this
     */
    public function setBodyText($bodyText)
    {
        $this->bodyText = StringUtil::getTextFromHtml($bodyText);
        return $this;
    }

    /**
     * Get html body identification checksum.
     *
     * @return null|string
     */
    public function getBodyChecksum()
    {
        return $this->bodyChecksum;
    }

    /**
     * Generate and set html body identification checksum.
     *
     * @param string $body
     * @return $this
     */
    public function setBodyChecksum($body)
    {
        $this->bodyChecksum = md5($body);
        return $this;
    }

    /**
     * Get array of objects Link.
     *
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Add object Link to array.
     *
     * @param Link $link
     * @return $this
     */
    public function addLink(Link $link)
    {
        $this->links[] = $link;
        return $this;
    }

    /**
     * Create link from array data.
     *
     * @param array  $linkData
     * @param string $currentUri
     * @return Link
     */
    public function createLinkFromArray(array $linkData, $currentUri)
    {
        $link = new Link();
        if (isset($linkData['url']) && !empty(trim((string)$linkData['url']))) {
            $link->setHrefOriginal($linkData['url']);
            $link->setUrl($linkData['url']);
            $link->setType($linkData['url'], $currentUri);
        }
        if (isset($linkData['hrefOriginal']) && !empty(trim((string)$linkData['hrefOriginal']))) {
            $link->setHrefOriginal($linkData['hrefOriginal']);
        }
        if (isset($linkData['text']) && !empty(trim((string)$linkData['text']))) {
            $link->setText($linkData['text']);
        }
        if (isset($linkData['title']) && !empty(trim((string)$linkData['title']))) {
            $link->setTitle($linkData['title']);
        }
        if (isset($linkData['rel']) && !empty(trim((string)$linkData['rel']))) {
            $link->setNoFollow($linkData['rel']);
        }

        return $link;
    }

    /**
     * Clear links.
     *
     * @return $this
     */
    public function clearLinks()
    {
        $this->links = [];
        return $this;
    }
}