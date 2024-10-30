<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Xml;

use DOMNode;
use Exception;
use DOMElement;
use DOMDocument;
use UnexpectedValueException;
use Yuhzel\X8seco\Core\Xml\XmlArrayObject;

/**
 * Class XmlRpcParser
 *
 * Handles the conversion of XML-RPC structured data into PHP data types
 *
 * Author: Yuhzel
 */
class XmlRpcParser
{
    public function __construct(private DOMDocument $doc) {}

    #region Request
    /**
     * Create an XML-RPC request from method name and arguments.
     *
     * @param string $methodName
     * @param mixed $args
     * @return string
     */
    public function createXml(string $methodName, mixed $args): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $methodCall = $dom->createElement('methodCall');
        $dom->appendChild($methodCall);

        $methodNameElement = $dom->createElement('methodName', $methodName);
        $methodCall->appendChild($methodNameElement);

        $params = $dom->createElement('params');
        $methodCall->appendChild($params);

        if (is_array($args)) {
            $this->arrayToXml($args, $params, $dom);
        } else {
            $this->addParam($args, $params, $dom);
        }

        return $dom->saveXML();
    }

    public function createMultiXml(string $methodName, array $calls)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // Create <methodCall>
        $methodCall = $dom->createElement('methodCall');
        $dom->appendChild($methodCall);

        // Create <methodName> system.multicall
        $methodNameElement = $dom->createElement('methodName', $methodName);
        $methodCall->appendChild($methodNameElement);

        // Create <params> for multicall
        $params = $dom->createElement('params');
        $methodCall->appendChild($params);

        if (count($calls) === 1) {
            $args = $calls[0]['params'];
            $login = $args['login'] ?? null;
            $manialink = $args['manialink'] ?? null;
            $duration = $args['duration'] ?? null;
            $display = $args['display'] ?? null;

            if (isset($login)) {
                $this->addParam($login, $params, $dom);
            }
            // Add manialink
            if (isset($manialink)) {
                $this->addParam($manialink, $params, $dom);
            }
            if (isset($duration)) {
                $this->addParam($duration, $params, $dom);
            }
            if (isset($display)) {
                $this->addParam($display, $params, $dom);
            }
            return $dom->saveXML();
        } else {
            foreach ($calls as $call) {
                $args = $call['params'];
                $login = $args['login'] ?? null;
                $manialink = $args['manialink'] ?? null;
                $duration = $args['duration'] ?? null;
                $display = $args['display'] ?? null;
            }
            if (isset($login)) {
                $this->addParam($login, $params, $dom);
            }
            // Add manialink
            if (isset($manialink)) {
                $this->addParam($manialink, $params, $dom);
            }
            if (isset($duration)) {
                $this->addParam($duration, $params, $dom);
            }
            if (isset($display)) {
                $this->addParam($display, $params, $dom);
            }
            return $dom->saveXML();
        }
    }

    /**
     * Converts an array of arguments to XML-RPC formatted XML.
     *
     * @param array $args
     * @param DOMElement $paramsElement
     * @param DOMDocument $dom
     * @return void
     */
    private function arrayToXml(array $args, DOMElement $paramsElement, DOMDocument $dom): void
    {
        foreach ($args as $arg) {
            $this->addParam($arg, $paramsElement, $dom);
        }
    }

    /**
     * Adds a single parameter to the XML.
     *
     * @param mixed $arg
     * @param DOMElement $paramsElement
     * @param DOMDocument $dom
     */
    private function addParam(mixed $arg, DOMElement $paramsElement, DOMDocument $dom): void
    {
        $param = $dom->createElement('param');
        $valueElement = $dom->createElement('value');
        $typeElement = $this->determineType(gettype($arg), $arg, $dom);
        $valueElement->appendChild($typeElement);
        $param->appendChild($valueElement);
        $paramsElement->appendChild($param);
    }

    /**
     * Determine the XML-RPC data type for a given PHP value and return the corresponding XML element.
     *
     * @param string $type
     * @param mixed $value
     * @param DOMDocument $dom
     * @return DOMElement
     */
    private function determineType(string $type, mixed $value, DOMDocument $dom): DOMElement
    {
        return match ($type) {
            'string' => $this->createStringElement($value, $dom),
            'boolean' => $dom->createElement('boolean', $value ? '1' : '0'),
            'integer' => $dom->createElement('int', htmlspecialchars((string)$value, ENT_XML1, 'UTF-8')),
            'double' => $dom->createElement('double', htmlspecialchars((string)$value, ENT_XML1, 'UTF-8')),
            'array' => $this->arrayToXmlElement($value, $dom),
            'object' => $this->structToXmlElement($value, $dom),
            'NULL' => $dom->createElement('string', 'null'),
            default => throw new UnexpectedValueException("Unsupported data type: $type")
        };
    }

    /**
     * Creates a string element, adding CDATA if it contains Manialink XML.
     *
     * @param string $value
     * @param DOMDocument $dom
     * @return DOMElement
     */
    private function createStringElement(string $value, DOMDocument $dom): DOMElement
    {
        if (strpos($value, '<manialink') !== false) {
            $stringElement = $dom->createElement('string');
            $cdata = $dom->createCDATASection($value);
            $stringElement->appendChild($cdata);
            return $stringElement;
        } else {
            $stringElement = $dom->createElement('string');
            $stringElement->appendChild($dom->createTextNode($value));
        }
        return $stringElement;
    }

    /**
     * Convert an array to an XML <array> element.
     *
     * @param array $array
     * @param DOMDocument $dom
     * @return DOMElement
     */
    private function arrayToXmlElement(array $array, DOMDocument $dom): DOMElement
    {
        $arrayElement = $dom->createElement('array');
        $dataElement = $dom->createElement('data');
        $arrayElement->appendChild($dataElement);

        foreach ($array as $value) {
            $valueElement = $dom->createElement('value');
            $typeElement = $this->determineType(gettype($value), $value, $dom);
            $valueElement->appendChild($typeElement);
            $dataElement->appendChild($valueElement);
        }

        return $arrayElement;
    }

    /**
     * Convert an object (struct) to an XML <struct> element.
     *
     * @param object $object
     * @param DOMDocument $dom
     * @return DOMElement
     */
    private function structToXmlElement(object $object, DOMDocument $dom): DOMElement
    {
        $structElement = $dom->createElement('struct');

        foreach (get_object_vars($object) as $key => $value) {
            $memberElement = $dom->createElement('member');

            $nameElement = $dom->createElement('name', htmlspecialchars((string)$key));
            $valueElement = $dom->createElement('value');
            $typeElement = $this->determineType(gettype($value), $value, $dom);

            $valueElement->appendChild($typeElement);
            $memberElement->appendChild($nameElement);
            $memberElement->appendChild($valueElement);
            $structElement->appendChild($memberElement);
        }

        return $structElement;
    }
    #endregion

    #region Response
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
        $faultObject->faultCode = (int) ($fault->getElementsByTagName('faultCode')->item(0)->nodeValue ?? 0);
        $faultObject->faultString = (string) ($fault->getElementsByTagName('faultString')->item(0)->nodeValue ?? '');

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
                //If the <value> is an array, process the array
                if ($valueElement->nodeName === 'array') {
                    $results['result'] = $this->processArray($valueElement);
                    return $results;
                }
                //If the <value> is a struct, process the struct
                elseif ($valueElement->nodeName === 'struct') {
                    $results['result'] = $this->processStruct($valueElement);
                    return $results;
                }
                //Otherwise, just process the value as a simple type (string, int, etc.)
                else {
                    $results['result'] = $this->processValue($valueElement);
                    return $results;
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
                'nil' => null,
                default => throw new UnexpectedValueException("Unknown type: {$element->tagName}"),
            };
        }
        return new UnexpectedValueException("IDK");
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

        if ($dataElements) {
            foreach ($dataElements->getElementsByTagName('value') as $valueElement) {
                if ($valueElement instanceof DOMElement) {
                    // Process each value element and add it to the array
                    $parsedValue = $this->processValue($valueElement->firstChild);
                    $array[] = $parsedValue;
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
        foreach ($element->getElementsByTagName('member') as $memberElement) {
            if ($memberElement instanceof DOMElement) {
                // Extract the <name> and the corresponding <value>
                $name = $memberElement->getElementsByTagName('name')->item(0)?->nodeValue;
                $valueElement = $memberElement->getElementsByTagName('value')->item(0)?->firstChild;
                // Ensure both name and value are valid
                if ($name !== null && $valueElement instanceof DOMElement) {
                    $struct->$name = $this->processValue($valueElement);
                }
            }
        }

        return $struct;
    }
    #endregion
}
