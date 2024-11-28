<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Services;

use RuntimeException;
use Yuhzel\X8seco\Services\HttpClient;
use Yuhzel\X8seco\Core\Xml\XmlRpcService;
use Yuhzel\X8seco\Core\Xml\XmlArrayObject;

class DedimaniaClient
{
    private string $endpoint = 'http://dedimania.net:8002/Dedimania';

    public function __construct(private XmlRpcService $xmlRpcService) {}

    public function request(string $fileName, array $params, ?string $endpoint = null): XmlArrayObject
    {
        $this->endpoint = $endpoint ?? $this->endpoint;
        $xmlTemplate = '';
        $xmlTemplate = @file_get_contents("{$this->xmlRpcService->path}/{$fileName}.xml");
        
        if (!$xmlTemplate) {
            throw new RuntimeException("Failed to load {$this->xmlRpcService->path}/{$fileName}.xml");
        }
        
        foreach ($params as $key => $value) {
            $xmlTemplate = str_replace(
                "%$key%",
                htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'),
                $xmlTemplate
            );
        }

        $xmlTemplate = str_replace(["\t", "\r", "\n", " "], "", $xmlTemplate);

        $headers = [
            'User-Agent: XMLaccess',
            'Cache-Control: no-cache',
            'Content-Type: text/xml; charset=UTF-8',
            'Accept-Encoding: gzip, deflate',
            'Content-Length: ' . strlen($xmlTemplate),
            'Keep-Alive: timeout=600, max=2000',
            'Connection: Keep-Alive',
        ];

        $httpClient = new HttpClient();
        $response = $httpClient->post($this->endpoint, $xmlTemplate, $headers);

        $parsed = $this->xmlRpcService->parseResponse($response)['parsed'];

        if (isset($parsed[0]['faultString'])) {
            return $parsed[0];
        }

        $parsed = Aseco::filterResponse($parsed);
        return $parsed[0];
    }
}
