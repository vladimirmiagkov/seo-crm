<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Entity\Keyword;
use AppBundle\Entity\SearchEngine;
use AppBundle\Entity\Traits\IdTrait;
use AppBundle\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialization;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * KeywordCompetitor - this is one site in SERP.
 * When we parse search engine for keywords positions, we can find competitors (other sites).
 *
 * @ORM\Table(name="keyword_competitor")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\KeywordCompetitorRepository")
 * @ORM\HasLifecycleCallbacks
 */
class KeywordCompetitor
{
    use IdTrait;
    use CreatedAtTrait;

    /**
     * Highlight words, for example, in title. Like "<rshighlight>Great</rshighlight> wikipedia"
     * Just a tag for visual highlight.
     */
    const HIGHLIGHT_WORD_TAG = 'rshighlight';

    /**
     * Linked keyword.
     *
     * @var Keyword
     *
     * @ORM\ManyToOne(targetEntity="Keyword")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * //@Serialization\Groups({"list"})
     * //@Serialization\MaxDepth(1)
     */
    protected $keyword;

    /**
     * Linked search engine.
     *
     * @var SearchEngine
     *
     * @ORM\ManyToOne(targetEntity="SearchEngine")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * //@Serialization\Groups({"list"})
     * //@Serialization\MaxDepth(1)
     */
    protected $searchEngine;

    /**
     * SEO position for keyword in search engine.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * //@Serialization\Groups({"list"})
     */
    protected $position;

    /**
     * Site url in SERP.
     * Like: "https://www.example.com/123.html?shownews=1#top" | null
     *
     * @var null|string
     *
     * @ORM\Column(type="text", length=65535, nullable=true)
     * //@Serialization\Groups({"list"})
     */
    protected $url;

    /**
     * Only domain. Like "www.example.com"
     *
     * @var null|string
     *
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    protected $domain; // TODO: do we need this?

    /**
     * Document modification time. Unix timestamp.
     *
     * @var null|int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $documentModificationTime;

    /**
     * Document charset. Like "utf-8".
     *
     * @var null|string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $documentCharset;

    /**
     * Document language. Like "en".
     *
     * @var null|string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $documentLang;

    /**
     * Document mime-type. Like "text/html".
     *
     * @var null|string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $documentMimeType;

    /**
     * Title in search engine.(Main title)
     * YANDEX: Заголовок найденного документа.
     *
     * @var null|string
     *
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    protected $documentTitle;

    /**
     * Headline in search engine. (Description)
     * YANDEX: Для формирования используется HTML-тег meta, содержащий атрибут name со значением «description»
     *
     * @var null|string
     *
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    protected $documentHeadline;

    /**
     * Only YANDEX search engine?
     * Document passages type.
     * YANDEX: Тип пассажа. Возможные значения:
     *     «0» — стандартный пассаж (сформирован из текста документа);
     *     «1» — пассаж на основе текста ссылки. Используется, если документ найден по ссылке.
     *
     * @var null|string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $documentPassagesType;

    /**
     * Only YANDEX search engine?
     * Passages. Text snippets for site in search engine. May be > 1.
     * YANDEX: Пассаж — это фрагмент найденного документа, содержащий слова запроса.
     *         Пассажи используются для формирования сниппетов — текстовых аннотаций к найденному документу.
     *
     * @var null|string[]
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $documentPassages;

    /**
     * Url in search engine, leads to "saved copy".
     *
     * @var null|string
     *
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    protected $savedCopyUrl;


    public function __construct()
    {
    }


    /**
     * @return Keyword
     */
    public function getKeyword(): Keyword
    {
        return $this->keyword;
    }

    /**
     * @param Keyword $keyword
     * @return $this
     */
    public function setKeyword(Keyword $keyword)
    {
        $this->keyword = $keyword;
        return $this;
    }

    /**
     * @return SearchEngine
     */
    public function getSearchEngine(): SearchEngine
    {
        return $this->searchEngine;
    }

    /**
     * @param SearchEngine $searchEngine
     * @return $this
     */
    public function setSearchEngine(SearchEngine $searchEngine)
    {
        $this->searchEngine = $searchEngine;
        return $this;
    }


    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition(int $position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = !empty($url) ? \strtolower(\trim($url)) : null;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = !empty($domain) ? \strtolower(\trim($domain)) : null;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDocumentModificationTime()
    {
        return $this->documentModificationTime;
    }

    /**
     * @param int|null $timestamp
     * @return $this
     */
    public function setDocumentModificationTime($timestamp)
    {
        $this->documentModificationTime = !empty($timestamp) ? (int)$timestamp : null;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDocumentCharset()
    {
        return $this->documentCharset;
    }

    /**
     * @param null|string $documentCharset
     * @return $this
     */
    public function setDocumentCharset($documentCharset)
    {
        $this->documentCharset = !empty($documentCharset) ? \strtolower(\trim($documentCharset)) : null;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDocumentLang()
    {
        return $this->documentLang;
    }

    /**
     * @param null|string $documentLang
     * @return $this
     */
    public function setDocumentLang($documentLang)
    {
        $this->documentLang = !empty($documentLang) ? \strtolower(\trim($documentLang)) : null;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDocumentMimeType()
    {
        return $this->documentMimeType;
    }

    /**
     * @param null|string $documentMimeType
     * @return $this
     */
    public function setDocumentMimeType($documentMimeType)
    {
        $this->documentMimeType = !empty($documentMimeType) ? \strtolower(\trim($documentMimeType)) : null;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDocumentTitle()
    {
        return $this->documentTitle;
    }

    /**
     * @param null|string $documentTitle
     * @return $this
     */
    public function setDocumentTitle($documentTitle)
    {
        $this->documentTitle = !empty($documentTitle) ? \trim(preg_replace('/\s+/', ' ', $documentTitle)) : null;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDocumentHeadline()
    {
        return $this->documentHeadline;
    }

    /**
     * @param null|string $documentHeadline
     * @return $this
     */
    public function setDocumentHeadline($documentHeadline)
    {
        $this->documentHeadline = !empty($documentHeadline) ? \trim(preg_replace('/\s+/', ' ', $documentHeadline)) : null;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDocumentPassagesType()
    {
        return $this->documentPassagesType;
    }

    /**
     * @param null|string $documentPassagesType
     * @return $this
     */
    public function setDocumentPassagesType($documentPassagesType)
    {
        $this->documentPassagesType = !empty($documentPassagesType) ? \strtolower(\trim($documentPassagesType)) : null;
        return $this;
    }

    /**
     * @return null|\string[]
     */
    public function getDocumentPassages()
    {
        return $this->documentPassages;
    }

    /**
     * @param null|string $documentPassage
     * @return $this
     */
    public function addDocumentPassage($documentPassage)
    {
        $this->documentPassages[] = \trim(preg_replace('/\s+/', ' ', $documentPassage));
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSavedCopyUrl()
    {
        return $this->savedCopyUrl;
    }

    /**
     * @param null|string $savedCopyUrl
     * @return $this
     */
    public function setSavedCopyUrl($savedCopyUrl)
    {
        $this->savedCopyUrl = !empty($savedCopyUrl) ? \strtolower(\trim($savedCopyUrl)) : null;
        return $this;
    }
}