<?php

namespace zap\util;

class Xml
{
    /**
     * 从 HTML 加载
     *
     * @throws \Exception
     */
    public static function loadHtml(string $input, array $options = [])
    {
        $defaults = [
            'return' => 'simplexml',
        ];
        $options += $defaults;

        $prevUseErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $xml = new \DOMDocument();
        $xml->loadHTML($input);

        libxml_use_internal_errors($prevUseErrors);

        if ($options['return'] === 'simplexml') {
            $xml = \simplexml_import_dom($xml);
        }

        if ($xml === false) {
            throw new \Exception('Failed to parse HTML');
        }

        return $xml;
    }

    /**
     * 从 XML 加载
     *
     * @throws \Exception
     */
    public static function loadXml(string $input, array $options = []): \SimpleXMLElement
    {
        $defaults = [
            'return' => 'simplexml',
            'flags'  => LIBXML_NOCDATA,
        ];
        $options += $defaults;

        $prevUseErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $xml = new \SimpleXMLElement($input, $options['flags']);
        } catch (\Exception $e) {
            $errors = libxml_get_errors();
            libxml_use_internal_errors($prevUseErrors);
            libxml_clear_errors();
            if (!empty($errors)) {
                throw new \Exception('XML Parse Error: ' . $errors[0]->message);
            }
            throw $e;
        }

        libxml_use_internal_errors($prevUseErrors);
        return $xml;
    }

    /**
     * SimpleXMLElement 转为数组
     */
    public static function toArray(\SimpleXMLElement $xml): array
    {
        $json = json_encode($xml);
        return json_decode($json, true) ?: [];
    }

    /**
     * 转换为 JSON
     */
    public static function toJson(\SimpleXMLElement $xml, int $flags = 0)
    {
        return json_encode($xml, $flags);
    }
}
