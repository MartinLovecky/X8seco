<?php

declare(strict_types=1);

namespace Yuhzel\X8seco\Services;

use RuntimeException;
use Yuhzel\X8seco\Services\HttpClient;
use Yuhzel\X8seco\Core\Xml\XmlRpcService;
use Yuhzel\X8seco\Core\Xml\XmlArrayObject;

class DedimaniaClient
{

    public function __construct(
        private XmlRpcService $xmlRpcService,
        private HttpClient $httpClient,
    ) {}

    public function authenticate(array $params): XmlArrayObject
    {
        $xmlTemplate = '';
        $xmlTemplate = @file_get_contents("{$this->xmlRpcService->path}/auth.xml");
        foreach ($params as $key => $value) {
            $xmlTemplate = str_replace("%$key%", $value, $xmlTemplate);
        }

        if (!$xmlTemplate) {
            throw new RuntimeException("Failed to load {$this->xmlRpcService->path}/auth.xml");
        }
        $xmlTemplate = str_replace(["\t", "\r", "\n"], "", $xmlTemplate);
        $headers = [
            'User-Agent: XMLaccess',
            'Cache-Control: no-cache',
            'Content-Type: text/xml; charset=UTF-8',
            'Accept-Encoding: gzip, deflate',
            'Content-Length: ' . strlen($xmlTemplate),
            'Keep-Alive: timeout=600, max=2000',
            'Connection: Keep-Alive',
        ];

        $this->httpClient->setTimeout(300);
        $response = $this->httpClient->post('http://dedimania.net:8002/Dedimania', $xmlTemplate, $headers);
        //NOTE - We just care about 1st response rest is uselesss garbage
        if ($response) {
            $parsed = $this->xmlRpcService->parseResponse($response)['parsed'];
            if (isset($parsed[0]['faultString'])) {
                return $parsed[0];
            }

            return $this->xmlRpcService->filterParsedResponse($parsed)[0];
        }

        $xmlArrayObejct = new XmlArrayObject();
        $xmlArrayObejct->faultString = 'Post request failed to http://dedimania.net:8002/Dedimania';
        return $xmlArrayObejct;
    }

    public function connectionAlive(string $endpoint = ''): bool
    {
        return $this->httpClient->alive($endpoint);
    }
}
