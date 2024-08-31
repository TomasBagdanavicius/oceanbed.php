<?php

declare(strict_types=1);

namespace LWP\Dom;

class Dom
{
    private \DOMDocument $doc;
    private \DOMXPath $xpath;


    public function __construct(
        public readonly string $html_source
    ) {

        // Prevent from showing warnings regarding HTML5 tags
        libxml_use_internal_errors(use_errors: true);

        $this->doc = new \DOMDocument();
        $this->doc->validateOnParse = false;

        $this->doc->loadHTML(
            // This is an attempt to replace the deprecated "HTML-ENTITIES" in `mb_convert_encoding($html_source, 'HTML-ENTITIES', 'UTF-8')`
            mb_encode_numericentity($html_source, [0x80, 0xffff, 0, 0xffff], 'UTF-8')
        );

        // Construct the XPath
        $this->xpath = new \DOMXPath($this->doc);
    }


    // Gets DOM document.

    public function getDoc(): \DOMDocument
    {

        return $this->doc;
    }


    // Gets XPath object.

    public function getXPath(): \DOMXPath
    {

        return $this->xpath;
    }


    // Handles a XPath query and on success returns the "DOMNodeList" object.

    public function queryAll(string $query_str): \DOMNodeList
    {

        $entries = $this->xpath->query($query_str);

        if (!$entries) {
            throw new Exceptions\InvalidXPathQueryException(sprintf("DOM XPath query \"%s\" appears to be invalid.", $query_str));
        }

        if (!$entries->length) {
            throw new Exceptions\EmptyXPathQueryResultException(sprintf("DOM XPath query \"%s\" did not yield any results.", $query_str));
        }

        return $entries;
    }


    // Handles a XPath query and on success returns the first node element from the DOM node list.

    public function queryOne(string $query_str): \DOMElement
    {

        $entries = $this->queryAll($query_str);

        foreach ($entries as $node) {

            return $node;
        }
    }


    // Find all elements containing all provided class names.

    public function queryByClassNames(array $classnames): \DOMNodeList
    {

        $query_str = self::buildClassQuery($classnames);

        $entries = $this->xpath->query($query_str);

        return $entries;
    }


    // Builds multi class query string for the XPath.

    public static function buildClassQuery(string|array $classnames, string $operator = 'and'): string
    {

        // Mind the dot in the beginning.
        // It will perform a relative query, based on the second argument added to "query".
        $format_query = ".//*[%s]";
        $format_contains = "contains(concat(' ', normalize-space(@class), ' '), '%s')";

        $str = self::arrayToStrFormat($classnames, $format_contains, ' ' . $operator . ' ');

        return sprintf($format_query, $str);
    }


    // Finds the next element sibling. This function excludes text nodes.

    public function getNextElement(\DOMNode $node): \DOMElement
    {

        do {

            $next = $node->nextSibling;

            if ($next->nodeType !== 3) {
                break;
            }

        } while ($next);

        return $next;
    }


    //

    public function getNextElement_Alt(\DOMNode $node): \DOMElement
    {

        return $this->xpath->query("following-sibling::*[1]", $node)->item(0);
    }


    //

    public function findByTagID(string $id, string $tagname = '*'): \DOMNodeList
    {

        $expression = ('//' . $tagname . '[@id="' . $id . '"]');

        $elements = $this->xpath->query($expression);

        if (!$elements->length) {
            throw new Exceptions\EmptyXPathQueryResultException(sprintf("DOM XPath query \"%s\" did not yield any results.", $expression));
        }

        return $elements;
    }


    //

    public function DOMInnerHTML($node)
    {

        $inner_html = '';
        $children = $node->childNodes;

        foreach ($children as $child) {

            $inner_html .= $node->ownerDocument->saveHTML($child);
        }

        return $inner_html;
    }


    //

    public function outerHTML($e)
    {

        $doc = new \DOMDocument();
        $doc->appendChild($doc->importNode($e, true));

        return $doc->saveHTML();
    }


    //

    public static function arrayToStrFormat($data, $format, $join)
    {

        if (is_array($data)) {

            $str = '';
            $length = count($data);
            $i = 1;

            foreach ($data as $d) {

                if ($i > 1) {
                    $str .= $join;
                }

                $str .= sprintf($format, $d);

                $i++;
            }

        } elseif (is_string($data)) {

            $str = sprintf($format, $data);

        }

        return $str;
    }
}
