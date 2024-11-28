<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Xml;

use DOMNode;
use DOMXPath;
use DOMElement;
use DOMDocument;
use Yuhzel\X8seco\Services\Aseco;
use Yuhzel\X8seco\Exceptions\XmlParserException;

class XmlRpcRequest
{

    private string $xmlPath = '';

    public function __construct(private DOMDocument $dom)
    {
        $this->xmlPath = Aseco::path() . 'app' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR;
    }

    /**
     * Create an XML-RPC request from method name and arguments.
     *
     * @param string $methodName
     * @param mixed $args
     * @return string
     */
    public function createXml(string $methodName, mixed $args): string|false
    {
        Aseco::$method = $methodName; //NOTE - sets method info for debug
        $this->dom = new DOMDocument('1.0', 'UTF-8'); //NOTE - we need clean DOMDocument 
        $this->dom->formatOutput = true;
        $methodCall = $this->dom->createElement('methodCall');
        $this->dom->appendChild($methodCall);

        $methodNameElement = $this->dom->createElement('methodName', $methodName);
        $methodCall->appendChild($methodNameElement);

        $params = $this->dom->createElement('params');
        $methodCall->appendChild($params);

        if (is_array($args)) {
            foreach ($args as $arg) {
                $this->addParam($arg, $params);
            }
        } else {
            $this->addParam($args, $params);
        }

        return $this->dom->saveXML();
    }

    /**
     * Adds a single parameter to the XML.
     *
     * @param mixed $arg
     * @param DOMElement $paramsElement
     */
    private function addParam(mixed $arg, DOMElement $paramsElement): void
    {
        $param = $this->dom->createElement('param');
        $valueElement = $this->dom->createElement('value');
        $typeElement = $this->determineType(gettype($arg), $arg);
        $valueElement->appendChild($typeElement);
        $param->appendChild($valueElement);
        $paramsElement->appendChild($param);
    }

    /**
     * Determine the XML-RPC data type for a given PHP value and return the corresponding XML element.
     *
     * @param string $type
     * @param mixed $value
     * @return DOMElement
     */
    private function determineType(string $type, mixed $value): DOMElement
    {
        if ($type === 'array' && $this->isAssociativeArray($value)) {
            // Treat associative array as a struct
            return $this->structToXmlElement((object) $value);
        }

        return match ($type) {
            'string' => $this->createStringElement($value),
            'boolean' => $this->dom->createElement('boolean', $value ? '1' : '0'),
            'integer' => $this->dom->createElement('int', htmlspecialchars((string)$value, ENT_XML1, 'UTF-8')),
            'double' => $this->dom->createElement('double', htmlspecialchars((string)$value, ENT_XML1, 'UTF-8')),
            'array' => $this->arrayToXmlElement($value),
            'object' => $this->structToXmlElement($value),
            'NULL' => $this->dom->createElement('string', 'null'),
            default => throw new XmlParserException("Unsupported data type: $type")
        };
    }

    /**
     * Creates a string element, adding CDATA if it contains Manialink XML.
     *
     * @param string $value
     * @return DOMElement
     */
    private function createStringElement(string $value): DOMElement
    {
        if (strpos($value, '<manialink') !== false) {
            $stringElement = $this->dom->createElement('string');
            $cdata = $this->dom->createCDATASection($value);
            $stringElement->appendChild($cdata);
            return $stringElement;
        } else {
            $stringElement = $this->dom->createElement('string');
            $stringElement->appendChild($this->dom->createTextNode($value));
        }
        return $stringElement;
    }

    /**
     * Convert an array to an XML <array> element.
     *
     * @param array $array
     * @return DOMElement
     */
    private function arrayToXmlElement(array $array): DOMElement
    {
        $arrayElement = $this->dom->createElement('array');
        $dataElement = $this->dom->createElement('data');
        $arrayElement->appendChild($dataElement);

        foreach ($array as $value) {
            $valueElement = $this->dom->createElement('value');
            if (is_array($value) && $this->isAssociativeArray($value)) {
                $typeElement = $this->structToXmlElement((object)$value);
            } else {
                $typeElement = $this->determineType(gettype($value), $value);
            }
            $valueElement->appendChild($typeElement);
            $dataElement->appendChild($valueElement);
        }

        return $arrayElement;
    }

    /**
     * Convert an object (struct) to an XML <struct> element.
     *
     * @param object $object
     * @return DOMElement
     */
    private function structToXmlElement(object $object): DOMElement
    {
        $structElement = $this->dom->createElement('struct');

        foreach (get_object_vars($object) as $key => $value) {
            $memberElement = $this->dom->createElement('member');

            $nameElement = $this->dom->createElement('name', htmlspecialchars((string)$key));
            $valueElement = $this->dom->createElement('value');
            $typeElement = $this->determineType(gettype($value), $value);

            $valueElement->appendChild($typeElement);
            $memberElement->appendChild($nameElement);
            $memberElement->appendChild($valueElement);
            $structElement->appendChild($memberElement);
        }

        return $structElement;
    }

    /**
     * Helper function to determine if an array is associative.
     *
     * @param array $array
     * @return bool
     */
    private function isAssociativeArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function canLoadFile(string $filePath): bool
    {
        if (!file_exists($filePath) ||  !is_readable($filePath)) {
            return false;
        }
        return true;
    }
}
