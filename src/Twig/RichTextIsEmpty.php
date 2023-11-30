<?php

declare(strict_types=1);


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RichTextIsEmpty extends AbstractExtension
{

    protected $emptyXmlString = '<?xml version="1.0" encoding="UTF-8"?><section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ibexa.co/xmlns/dxp/docbook/xhtml" xmlns:ezcustom="http://ibexa.co/xmlns/dxp/docbook/custom" version="5.0-variant ezpublish-1.0"/>';
    protected $emptyXmlStringLength = 281; // <xml .... with 2 \n characters
    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('richtext_is_empty', [
                $this,
                'richtext_is_empty',
            ]),
        ];
    }

    public function richtext_is_empty($val)
    {

        // expect DomDocument but could be something else (safe fail)
        if(is_string($val)) {
            return empty($val) ? true : false;
        }

        /** @var \DOMDocument $val */
        if(is_object($val)) {
            try{
                // remove line breaks and whitespace to bare minimum for comparison with length
                $xmlString = trim(preg_replace('/\s\s+/', ' ', $val->saveXML()));
                if(strlen($xmlString) > $this->emptyXmlStringLength) {
                    return false;
                }

            } catch(\Exception $e) {
                // dump($e)
            }
        }
        return true;
    }
}
