<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Core\Xml;

use Yuhzel\X8seco\Core\Xml\{
    XmlRpcRequest,
    XmlRpcResponse,
    XmlArrayObject
};

/**
 * Class XmlRpcService
 *
 * Manages XML-RPC request creation and response.
 *
 * Author: Yuhzel
 */
class XmlRpcService
{
    public function __construct(
        private XmlRpcRequest $request,
        private XmlRpcResponse $response,
    ) {
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
        return $this->request->createMultiXml($methodName, $calls);
    }

    /**
     * Parse the XML-RPC response into a structured format.
     *
     * @param string $xml
     * @return XmlArrayObject
     */
    public function parseResponse(string $xml): XmlArrayObject
    {
        return $this->response->parseResponse($xml);
    }
}
