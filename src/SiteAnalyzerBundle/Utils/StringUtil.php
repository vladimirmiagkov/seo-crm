<?php
declare(strict_types=1);

namespace SiteAnalyzerBundle\Utils;

class StringUtil
{
    /**
     * Clear string from html tags and some special symbols.
     * We can't just use strip_tags, because we need more precise instrument.
     *
     * @param string $str
     * @return string
     */
    public static function getTextFromHtml($str): string
    {
        $str = (string)$str;
        $str = str_replace(array("\r", "\n", "\t"), ' ', $str);   //remove special symbols
        $str = self::clearSpecialHtmlTags($str);
        $str = preg_replace('#<[^<]+?>#u', ' ', $str);            //remove html tags
        $str = str_replace(array('&nbsp;'), ' ', $str);           //
        $str = html_entity_decode($str, ENT_NOQUOTES, 'UTF-8');   //decode
        $str = iconv('UTF-8', 'UTF-8', $str);                     //hm...
        $str = preg_replace('/\s\s+/u', ' ', $str);               //remove double spaces
        $str = trim($str);

        return $str;
    }

    /**
     * Clear string from some special tags.
     *
     * @param string $str
     * @return string
     */
    public static function clearSpecialHtmlTags($str): string
    {
        $str = (string)$str;
        $str = preg_replace('/<!--(.*?)-->/isu', ' ', $str);      //remove <!-- ...html_comment... -->
        $str = preg_replace('#(<script(.*?)>)(.*?)(</script>)#isu', ' ', $str); //remove
        $str = preg_replace('#(<style(.*?)>)(.*?)(</style>)#isu', ' ', $str); //remove
        //$str = preg_replace('#(<noscript(.*?)>)(.*?)(</noscript>)#isu', ' $1$4', $str); //remove noscript tags
        return $str;
    }
}