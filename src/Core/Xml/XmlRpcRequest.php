<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Xml;

use DOMDocument;
use DOMElement;
use UnexpectedValueException;
use Yuhzel\X8seco\Services\Basic;

class XmlRpcRequest
{
    /**
     * Create an XML-RPC request from method name and arguments.
     *
     * @param string $methodName
     * @param mixed $args
     * @return string
     */
    public function createXml(string $methodName, mixed $args): string|false
    {
        Basic::$method = $methodName; //NOTE - sets method info for debug
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $methodCall = $dom->createElement('methodCall');
        $dom->appendChild($methodCall);

        $methodNameElement = $dom->createElement('methodName', $methodName);
        $methodCall->appendChild($methodNameElement);

        $params = $dom->createElement('params');
        $methodCall->appendChild($params);

        if (is_array($args)) {
            foreach ($args as $arg) {
                $this->addParam($arg, $params, $dom);
            }
        } else {
            $this->addParam($args, $params, $dom);
        }

        return $dom->saveXML();
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
        if ($type === 'array' && $this->isAssociativeArray($value)) {
            // Treat associative array as a struct
            return $this->structToXmlElement((object) $value, $dom);
        }

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
            if (is_array($value) && $this->isAssociativeArray($value)) {
                $typeElement = $this->structToXmlElement((object)$value, $dom);
            } else {
                $typeElement = $this->determineType(gettype($value), $value, $dom);
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
}
