<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Xml;

use Yuhzel\X8seco\Core\Xml\{
    XmlRpcRequest,
    XmlRpcResponse,
    XmlArrayObject
};

<<<<<<< HEAD
use Yuhzel\X8seco\Services\Basic;

=======
>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8
/**
 * Class XmlRpcService
 *
 * Manages XML-RPC request creation and response.
 *
 * Author: Yuhzel
 */
class XmlRpcService
{
<<<<<<< HEAD

    public string $path = '';

=======
>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8
    public function __construct(
        private XmlRpcRequest $request,
        private XmlRpcResponse $response,
    ) {
<<<<<<< HEAD
        $this->path = Basic::path() . 'app' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'dedimania';
=======
>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8
    }

    /**
     * Create an XML-RPC request from method name and arguments.
     *
     * @param string $methodName
     * @param mixed $args
     * @return string|false
     */
    public function createRequest(string $methodName, mixed $args): string|false
    {
        return $this->request->createXml($methodName, $args);
    }

    /**
     * Create a multi-call XML-RPC request.
     *
     * @param string $methodName
     * @param array $calls
     * @return string|false
     */
    public function createMultiRequest(string $methodName, array $calls): string|false
    {
<<<<<<< HEAD
        return false; //return $this->request->createMultiXml($methodName, $calls);
=======
        return $this->request->createMultiXml($methodName, $calls);
>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8
    }

    /**
     * Parse the XML-RPC response into a structured format.
     *
     * @param string $xml
<<<<<<< HEAD
     * @return mixed
     */
    public function parseResponse(string $xml): mixed
    {
        return $this->response->parseResponse($xml);
    }

    public function filterParsedResponse(array $response): array
    {
        $xmlArrayObjects = array_filter($response, function ($item) {
            return $item instanceof XmlArrayObject;
        });

        return array_values($xmlArrayObjects);
    }
=======
     * @return XmlArrayObject
     */
    public function parseResponse(string $xml): XmlArrayObject
    {
        return $this->response->parseResponse($xml);
    }
>>>>>>> 321574d744f9007dec5eb4c240b049727c0fa8e8
}
