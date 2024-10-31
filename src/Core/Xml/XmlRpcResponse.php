<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Xml;

use DOMNode;
use Exception;
use DOMElement;
use DOMDocument;
use UnexpectedValueException;
use Yuhzel\X8seco\Core\Xml\XmlArrayObject;
use Yuhzel\X8seco\Services\Basic;

class XmlRpcResponse
{
    public function __construct(private DOMDocument $doc) {}

    /**
     * Converts the XML-RPC response into a structured array or XmlArrayObject.
     *
     * @return XmlArrayObject
     */
    public function parseResponse(string $xml): XmlArrayObject
    {
        if (!$this->doc->loadXML($xml)) {
            throw new Exception('Failed to load XML');
        }

        $root = $this->doc->documentElement;

        if (!$root instanceof DOMElement) {
            throw new Exception('Invalid XML structure');
        }

        $fault = $root->getElementsByTagName('fault')->item(0);
        if ($fault instanceof DOMElement) {
            return $this->processFault($fault);
        }

        $params = $root->getElementsByTagName('params')->item(0);
        if ($params instanceof DOMElement) {
            return $this->processParams($params);
        }

        throw new Exception('No valid response found');
    }

    /**
     * Process the <fault> element and its children, returning an XmlArrayObject with fault details.
     *
     * @param DOMElement $fault
     * @return XmlArrayObject
     */
    private function processFault(DOMElement $fault): XmlArrayObject
    {
        $faultObject = new XmlArrayObject();
        $faultObject->methodName = Basic::$method; //NOTE - DEBUG INFO

        // if fault is inside struct
        $struct = $fault->getElementsByTagName('struct')->item(0);
        if ($struct) {
            $members = $struct->getElementsByTagName('member');
            foreach ($members as $member) {
                if ($member instanceof DOMElement) {
                    $faultObject->faultCode = (int)$member->getElementsByTagName('name')->item(0)->nodeValue;
                    $faultObject->faultString = (string)$member->getElementsByTagName('value')->item(0)->firstChild->nodeValue;
                }
            }
            return $faultObject;
        }

        $faultObject->faultCode = (int) $fault->getElementsByTagName('faultCode')->item(0)->nodeValue;
        $faultObject->faultString = (string) $fault->getElementsByTagName('faultString')->item(0)->nodeValue;

        return $faultObject;
    }

    /**
     * Process <params> element and its children.
     *
     * @param DOMElement $params
     * @return XmlArrayObject
     */
    private function processParams(DOMElement $params): XmlArrayObject
    {
        $results = new XmlArrayObject();

        foreach ($params->getElementsByTagName('param') as $param) {
            if (!$param instanceof DOMElement) {
                continue; //Skip non-DOMElement
            }

            $valueElement = $param->getElementsByTagName('value')->item(0)?->firstChild;
            if ($valueElement instanceof DOMElement) {
                $result = $this->processValue($valueElement);
                if ($result instanceof XmlArrayObject) {
                    $results->add('result', $result);
                } else {
                    $results->add('parsed', $result);
                }
            }
        }

        return $results;
    }

    /**
     * Process the <value> element, which can contain various types of data.
     *
     * @param DOMNode|DOMElement $element
     * @return mixed
     */
    private function processValue(DOMNode|DOMElement $element): mixed
    {
        // If the current element is <value>, we want to process its child node
        if ($element->nodeName === 'value' && $element->firstChild instanceof DOMElement) {
            $element = $element->firstChild; // Move to the actual type element inside <value>
        }
        if ($element instanceof DOMElement) {
            return match ($element->tagName) {
                'string' => (string)$element->nodeValue,
                'boolean' => filter_var($element->nodeValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'int', 'i4' => (int)$element->nodeValue,
                'double' => (float)$element->nodeValue,
                'array' => $this->processArray($element),
                'struct' => $this->processStruct($element),
                'fault' => $this->processStruct($element),
                'nil' => null,
                default => throw new UnexpectedValueException("Unknown type: {$element->tagName}"),
            };
        }
        return new UnexpectedValueException("Unreachable");
    }

    /**
     * Process <array> element and its children.
     *
     * @param DOMElement $element
     * @return array
     */
    private function processArray(DOMElement $element): array
    {
        $array = [];
        $dataElements = $element->getElementsByTagName('data')->item(0);
        $addedValues = [];

        if ($dataElements) {
            foreach ($dataElements->getElementsByTagName('value') as $valueElement) {
                if ($valueElement instanceof DOMElement) {
                    // Process each value element and add it to the array
                    $parsedValue = $this->processValue($valueElement->firstChild);
                    if (!in_array($parsedValue, $addedValues, true)) {
                        $array[] = $parsedValue;
                        $addedValues[] = $parsedValue;
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Process <struct> element and its members.
     *
     * @param DOMElement $element
     * @return XmlArrayObject
     */
    private function processStruct(DOMElement $element): XmlArrayObject
    {
        $struct = new XmlArrayObject();
        $addedKeys = [];

        foreach ($element->getElementsByTagName('member') as $memberElement) {
            if ($memberElement instanceof DOMElement) {
                // Extract the <name> and the corresponding <value>
                $name = $memberElement->getElementsByTagName('name')->item(0)?->nodeValue;
                $valueElement = $memberElement->getElementsByTagName('value')->item(0)?->firstChild;
                // Ensure both name and value are valid
                if ($name !== null && $valueElement instanceof DOMElement) {
                    if (!in_array($name, $addedKeys, true)) {
                        $struct->$name = $this->processValue($valueElement);
                        $addedKeys[] = $name; // Mark this key as added
                    }
                }
            }
        }

        return $struct;
    }
}
