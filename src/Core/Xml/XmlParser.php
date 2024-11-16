<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Xml;

use DOMNode;
use DateTime;
use Exception;
use DOMElement;
use DOMDocument;
use DOMNodeList;
use Yuhzel\X8seco\Services\Aseco;
use Yuhzel\X8seco\Core\Xml\XmlArrayObject;

/**
 * XmlParser class for parsing XML files into an XmlArrayObject.
 *
 * This class provides methods to load XML data from a file, process its structure,
 * and convert it into a structured XmlArrayObject. It processes attributes and
 * child nodes, and attempts to intelligently convert data types such as booleans,
 * numbers, and DateTime formats.
 *
 * Note:
 * - This parser does not handle deeply nested XML structures gracefully.
 * - XML File must be in /app/xml folder
 * - XML comments are ignored in parsing.
 *
 * @package Yuhzel\X8seco\Core
 * @author Yuhzel
 */
class XmlParser
{
    /**
     * `/app/xml/`
     *
     * @var string
     */
    public string $xmlPath = '';

    /**
     * Initializes the XmlParser instance, setting the XML file path.
     */
    public function __construct()
    {
        $this->xmlPath = Aseco::path() . 'app' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR;
    }

    /**
     * Parses an XML file into an XmlArrayObject.
     *
     * It only supports XML files in which the root node has elements or a single child node.
     *
     * @param string $fileName The name of the XML file to parse.
     *
     * @return XmlArrayObject The parsed XML data as an XmlArrayObject.
     *
     * @throws Exception If the XML file cannot be loaded.
     */
    public function parseXml(string $fileName): XmlArrayObject
    {
        $dom = new DOMDocument();
        $dom->load($this->xmlPath . $fileName);
        $root = $dom->documentElement;
        if ($root->childNodes->length === 1 && $root->firstChild->nodeType == XML_ELEMENT_NODE) {
            return $this->parseNode($root->firstChild);
        }
        return $this->parseNode($root);
    }

    /**
     * Recursively parses a DOMElement node and converts it into an XmlArrayObject.
     *
     * @param DOMNode|DOMElement $node The DOMElement node to parse.
     *
     * @return XmlArrayObject The converted XmlArrayObject.
     */
    private function parseNode(DOMNode|DOMElement $node): XmlArrayObject
    {
        $dataObject = new XmlArrayObject();

        // Process attributes
        $attributes = $node->attributes;
        foreach ($attributes as $attr) {
            $dataObject->addAttribute($attr->nodeName, $this->convertValue($attr->nodeValue));
        }

        // Process child nodes
        $this->processChildNodes($node->childNodes, $dataObject);

        // Flatten XmlArrayObject if it contains a single element
        foreach ($dataObject as $key => $item) {
            if ($item instanceof XmlArrayObject && count($item) === 1 && isset($item['value'])) {
                $dataObject[$key] = $item['value'];
            }
        }

        return $dataObject;
    }

    /**
     * Processes child nodes of a DOMNodeList and adds them to the XmlArrayObject.
     *
     * @param DOMNodeList $childNodes The list of child nodes to process.
     * @param XmlArrayObject $dataObject The XmlArrayObject to populate with child nodes.
     *
     * @return void
     */
    private function processChildNodes(DOMNodeList $childNodes, XmlArrayObject &$dataObject): void
    {
        foreach ($childNodes as $childNode) {
            switch ($childNode->nodeType) {
                case XML_COMMENT_NODE:
                    continue 2; // Skip to the next child node

                case XML_TEXT_NODE:
                    $textValue = trim($childNode->nodeValue);
                    if ($textValue !== '') {
                        $dataObject['value'] = $this->convertValue($textValue);
                    }
                    break;

                default:
                    $tag = $childNode->nodeName;
                    $value = $this->parseNode($childNode);

                    // Handle multiple nodes with the same tag name
                    if (isset($dataObject[$tag])) {
                        if (!is_array($dataObject[$tag])) {
                            $dataObject[$tag] = [$dataObject[$tag]];
                        }
                        $dataObject[$tag][] = $value;
                    } else {
                        $dataObject[$tag] = $value;
                    }
                    break;
            }
        }
    }

    /**
     * Converts a value to its appropriate type based on its content.
     *
     * @param mixed $value The value to convert.
     *
     * @return mixed The converted value.
     */
    private function convertValue(mixed $value): mixed
    {
        if (is_string($value)) {
            // Convert boolean strings to actual boolean values
            $filteredValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($filteredValue !== null) {
                return $filteredValue;
            }

            // Handle numbers
            if (is_numeric($value)) {
                return strpos($value, '.') !== false ? (float) $value : (int) $value;
            }

            // Handle empty strings
            if ($value === '') {
                return null;
            }

            // Handle date/time strings
            if ($this->isDateTimeString($value)) {
                return new DateTime($value);
            }
        }

        return $value;
    }

    /**
     * Determines if a string is a valid date/time format.
     *
     * @param string $value The string to check.
     *
     * @return bool True if the string is a valid date/time format, false otherwise.
     */
    private function isDateTimeString(string $value): bool
    {
        try {
            new DateTime($value);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
