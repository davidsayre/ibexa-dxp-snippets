<?php

namespace App\Helper\Cleaners;

class HtmlToRichText3 {
    /**
     * Strip attributes off of these html tags
     * Be careful of partial matches like <em(bed)>
     *
     * @param string $sXhtml
     * @param array $aTagsToStrip Optional specific array of tag names to strip, otherwise calls getTagsToStrip()
     */
    public static function stripTagAttributes($sXhtml, $aTagsToStrip = null) {
        if(!$aTagsToStrip) {
            $aTagsToStrip = self::tagsStripAttributes();
        }
        //TODO single regex vs loop
        //$regex = "/<(".implode(' |',$aTagsToStrip)."+)[^>]*>/igm";
        //preg_replace($regex, '<$1>', $sXhtml);

        $sXhtmlClean = $sXhtml;
        foreach($aTagsToStrip as $sTag) {
            $sXhtmlClean = preg_replace('/<'.$sTag.' [^>]*>/Uis', '<'.$sTag.'>', $sXhtmlClean);
        }
        //failsafe
        if(empty($sXhtmlClean)) {
            return $sXhtml;
        }
        return $sXhtmlClean;

    }

    public static function tagsStripAttributes() {
        return array('p','h1','h2','h3','h4','h5','h6','strong','ol','ul','li','table','tr','td','th','span','em','sup','sub','br');
    }

    public static function richTextXmlPrep($sText, $utf_encode = true)
    {
        // Part 1 converting character encoding to HTML
        $sText = self::convertCP1252ToHTML($sText); //VERY IMPORTANT remove windows chars
        $sText = self::convertISOToHTML($sText);
        $sText = self::cleanupHTMLEncoded($sText); // Double encoding fixes

        //self::debug('POST - CLEANUP:'.$sText."\n");

        // Part 2; HTML to UTF8 (only do this ONCE, so allow optional)
        if($utf_encode === true) {
            $sText = self::convertHTMLtoUTF($sText);
        }

        // Part 3
        $sText = self::trimTagWhitespace($sText);
        $sText = self::stripTagAttributes($sText);
        $sText = self::cleanHTMLForRICHTEXT($sText);
        $sText = self::cleanLinksForRICHTEXT($sText);

        // part 4 (HOLD)
        //$sText = self::mapHtmlToRICHTEXT($sText);

        /* Optional */
        //$sText = self::cleanLinkAttributes($sText);

        // self::debug('POST - richtextPrep():'.$sText."\n");

        return $sText;
    }

    /**
     * Special <link> encode
     * Strip away <a href="javascript:*"> Text </a> leaving only the text.
     */
    public static function cleanLinksForRICHTEXT($sXhtml){

        $aReplacePatterns = array(
            //DO NOT PUT regex flags on rules! - added during processing
            array('/<a href="javascript[^"]*">(.*?)<\/a>/','$1'), //unlink javascript href's

        );

        foreach($aReplacePatterns as $regex){
            $sXhtml = preg_replace($regex[0]."Uis", $regex[1], $sXhtml);
        }

        return $sXhtml;
    }


    /**
     * Special ISO characters to HTML
     *
     * @param $str
     * @return string
     */
    public static function convertISOToHTML($str) {
        $detect_iso = mb_detect_encoding($str);
        // safeguard against encoding the wrong input character set
        if($detect_iso == 'ISO-8859-1') {
            $str = htmlentities($str, ENT_SUBSTITUTE | ENT_NOQUOTES, "ISO-8859-1",false);
        }
        return $str;
    }

    /**
     * Special double encoding fixes
     *
     * @param $str
     * @return mixed
     */
    public static function cleanupHTMLEncoded($str){
        // should be ok $str = str_replace('&amp;','&',$str);
        $str = str_replace('&nbsp;', " ", $str);
        $str = str_replace('&ndash;', '–', $str);
        $str = str_replace('&mdash;', '-', $str);
        $str = str_replace('&hellip;', '', $str);

        // special quotes
        $str = str_replace('&ldquo;','"',$str);
        $str = str_replace('&rdquo;','"',$str);

        //double encoded
        $str = str_replace('&amp;nbsp;',' ',$str);

        return $str;
    }



    /**
     * FINAL ENCODING
     *
     * UTF conversion from HTML should only be done ONCE!
     * Double UTF8 will cause special characters
     *
     * @param $str
     * @return mixed
     */
    public static function convertHTMLtoUTF ($str) {
        $str = html_entity_decode($str, ENT_QUOTES, "UTF-8");
        return $str;
    }

    /**
     * Regex to colapse EXTRA whitespace (must be 2 or more spaces after tag) into 1
     *
     * @param $sXhtml
     * @return string
     */
    public static function trimTagWhitespace($sXhtml) {
        // must ONLY use '/im' and NOT g
        // DECOM: return preg_replace('/>\s+/im', ">", $sXhtml);
        return preg_replace('/>\s\s+/im', "> ", $sXhtml);
    }


    public static function convertCP1252ToHTML($str) {

        // Round 1
        // Skipping < > & etc handled elsewhere
        $search = array(
            chr(212),
            chr(213),
            chr(210),
            chr(211),
            chr(209),
            chr(208),
            chr(201),
            chr(145),
            chr(146),
            chr(147),
            chr(148),
            chr(151),
            chr(150),
            chr(133),
            chr(194)
        );

        $replace = array(
            '&#8216;',
            '&#8217;',
            '&#8220;',
            '&#8221;',
            '&#8211;',
            '&#8212;',
            '&#8230;',
            '&#8216;',
            '&#8217;',
            '&#8220;',
            '&#8221;',
            '&#8211;',
            '&#8212;',
            '&#8230;',
            ''
        );

        $str = str_replace($search, $replace, $str);

        // Round 2:
        $quotes = array(
            "\xC2\xAB"     => '"', // « (U+00AB) in UTF-8
            "\xC2\xBB"     => '"', // » (U+00BB) in UTF-8
            "\xE2\x80\x98" => "'", // ‘ (U+2018) in UTF-8
            "\xE2\x80\x99" => "'", // ’ (U+2019) in UTF-8
            "\xE2\x80\x9A" => "'", // ‚ (U+201A) in UTF-8
            "\xE2\x80\x9B" => "'", // ‛ (U+201B) in UTF-8
            "\xE2\x80\x9C" => '"', // “ (U+201C) in UTF-8
            "\xE2\x80\x9D" => '"', // ” (U+201D) in UTF-8
            "\xE2\x80\x9E" => '"', // „ (U+201E) in UTF-8
            "\xE2\x80\x9F" => '"', // ‟ (U+201F) in UTF-8
            "\xE2\x80\xB9" => "'", // ‹ (U+2039) in UTF-8
            "\xE2\x80\xBA" => "'", // › (U+203A) in UTF-8
        );
        $str = strtr($str, $quotes);

        return $str;
    }

    /*
	 * Generic clean and strip tags in preparation for richtext parsing
	 * Run specific find/replaces BEFORE calling this 
	 * Run complex substitutions BEFORE calling this to avoid unwanted matches (for example <object> -> [OBJECT]) for html object tag to be pased later 
	 *
	 * @param string $sXhtml Valid XHTML/XML string
	 * @return string Cleaned XHTML with possible RICHTEXT mapped custom tags
	 */
    public static function cleanHTMLForRICHTEXT($sXhtml){

        $aReplacePatterns = array(

            //DO NOT PUT regex flags on rules! - added during processing		

            //Delete tags entirely
            array('/<!DOCTYPE[^>]*>/', ''), //remove DOCTYPE tag
            array('/<\/?html[^>]*>/', ''),
            array('/<\/?xml[^>]*>/', ''),
            array('/<\/?keyword[^>]*>/', ''),
            array('/<\/?font[^>]*>/', ''),
            array('/<\/?meta[^>]*>/', ''),
            array('/<\/?center[^>]*>/', ''),
            array('/<\/?illustration[^>]*>/', ''),
            array('/<\/?dynamic\.links[^>]*>/', ''), //remove dynamic.links tag
            array('/<\/?suppress[^>]*>/', ''),
            array('/<\/?div[^>]*>/', ''),           // remove divs (Careful!)
            array('/<\/?title[^>]*>/', ''),
            array('/<\/?body[^>]*>/', ''),
            array('/<\/?head[^>]*>/', ''),
            array('/<\/?tbody[^>]*>/', ''),
            array('/<\/?thead[^>]*>/', ''),
            array('/<\/?colgroup[^>]*>/', ''),
            array('/<\/?short_block[^>]*>/', ''),
            array('/<\/?components[^>]*>/', ''),

            /* Span is stripped so obfuscate if there are special mappings to be done later */
            array('/<\/?span[^>]*>/', ''),

            /* Clean attributes from tags */
            array('/<(\/?)blockquote>/', '<$1p>'), //remap block quote to <p>
            array('/<(\/?)uL[^>]*>/', '<$1ul>'), //remap uL to ul

            /* Misc encoded and paragaph related */
            array('/<p><p/', '<p'), //remove double <p>
            array('/<\/p><\/p>/', '</p>'),	 //remove double </p>		
            array('/<p *\/+>/', ''), //??
            array('/<p align="[^"]*">/', '<p>'), //??
            array('/<p>&nbsp;<\/p>/', ''), //remove empty
            array('/<p> <\/p>/', ''), //remove empty + space
            array('/<h1><\/h1>/', ''), //remove empty
            array('/<\/br>/', ''), //remove closing </br> as invalid
            array('/<p><br><\/p>/', ''), //remove <p> with only line break
            array('/<br>/',chr(0x0D).chr(0x0A)), // no more BR support use line breaks
            array('/<p>(<h\d>.*?<\/h\d>)<\/p>/is', '$1'), //??

            /* Remove Empty tags */
            array('/<b><\/b>/', ''), //remove empty
            array('/<i><\/i>/', ''), //remove empty
            array('/<p><\/p>/', ''), //remove empty			
            array('/<h2><\/h2>/', ''), //remove empty
            array('/<h3><\/h3>/', ''), //remove empty
            array('/<h4><\/h4>/', ''), //remove empty
            array('/<h5><\/h5>/', ''), //remove empty
            array('/<h6><\/h6>/', ''), //remove empty
            array('/<em><\/em>/', ''), //remove empty
            array('/<a><\/a>/', ''), //remove empty
            array('/<strong><\/strong>/', ''), //remove empty

            //remove comments
            array('/<!--(.*)-->/', ''),

        );

        foreach($aReplacePatterns as $regex){
            $sXhtml = preg_replace($regex[0]."Uis", $regex[1], $sXhtml);
        }

        return $sXhtml;
    }

    public static function mapHtmlToRICHTEXT($sXhtml) {
        $aReplacePatterns = array(
            //DO NOT PUT regex flags on rules! - added during processing
            /* RICHTEXT mapping tags */
            array('/<u>([^<]*)<\/u>/', '<custom name="underline">$1</custom>'),        // convert underline to richtext
            array('/<sup>([^<]*)<\/sup>/', '<custom name="sup">$1</custom>'),        // convert sup to richtext
            array('/<sub>([^<]*)<\/sub>/', '<custom name="sub">$1</custom>'),        // convert sub to richtext
            array('/<em>([^<]*)<\/em>/', '<i>$1</i>'),        // convert em to i
            array('/<a name="([^"]*)">(.*)<\/a>/','<anchor name="$1">$2</anchor>'), //convert anchor to richtext
        );

        foreach($aReplacePatterns as $regex){
            $sXhtml = preg_replace($regex[0]."Uis", $regex[1], $sXhtml);
        }

        return $sXhtml;
    }


}

?>