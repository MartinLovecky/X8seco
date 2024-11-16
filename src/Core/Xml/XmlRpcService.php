<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Xml;

use Yuhzel\X8seco\Core\Xml\{
    XmlRpcRequest,
    XmlRpcResponse,
    XmlArrayObject
};

use Yuhzel\X8seco\Services\Aseco;

/**
 * Class XmlRpcService
 *
 * Manages XML-RPC request creation and response.
 *
 * Author: Yuhzel
 */
class XmlRpcService
{
    public string $path = '';

    public function __construct(
        private XmlRpcRequest $request,
        private XmlRpcResponse $response,
    ) {
        $this->path = Aseco::path() . 'app' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'dedimania';
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
        return false; //return $this->request->createMultiXml($methodName, $calls);
    }

    /**
     * Parse the XML-RPC response into a structured format.
     *
     * @param string $xml
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
}
