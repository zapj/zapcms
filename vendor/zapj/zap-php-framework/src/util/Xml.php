<?php

namespace zap\util;

class Xml
{
    public static function loadHtml($input, $options = [])
    {
        $defaults = [
            'return' => 'simplexml'
        ];
        $options += $defaults;

        $xml = new \DOMDocument();
        $xml->loadHTML($input);

        if ($options['return'] === 'simplexml') {
            $xml = \simplexml_import_dom($xml);
        }

        return $xml;
    }

    /**
     * @throws \Exception
     */
    public static function loadXml($input, $options = [])
    {
        $defaults = [
            'return' => 'simplexml',
            'flags' => LIBXML_NOCDATA
        ];
        $options += $defaults;

       $xml = new \SimpleXMLElement($input,$options['flags']);

       return $xml;
    }
}