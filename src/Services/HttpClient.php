<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Services;

//use __IDE\LanguageLevelTypeAware;
use Yuhzel\Xaseco\Services\Basic;

class HttpClient
{
    private string $baseUrl = '';
    //#[LanguageLevelTypeAware(['8.0' => 'CurlHandle|false'], default: 'resource|false')]
    private $ch;
    private $cert;
    private string $cookieFile = '';

    public function __construct(string $baseUrl = '')
    {
        $this->cert = Basic::path() . 'app/cacert.pem';
        $this->baseUrl = $baseUrl;
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CAINFO, $this->cert);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookieFile); // Save cookies
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile); // Reuse cookies
    }

    private function request(
        string $method,
        string $endpoint,
        array $params = [],
        array $headers = []
    ): string|bool {
        $url = $this->baseUrl . $endpoint;
        $method = strtoupper($method);

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        // Handle POST, PUT, DELETE payload
        if (in_array($method, ["POST", "PUT", 'DELETE'], true)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        // Set custom headers if provided
        if (!empty($headers)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            Basic::console('cURL error: ' . curl_error($this->ch));
            return false;
        }

        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200) {
            Basic::console('HTTP error: ' . $httpCode);
            return false;
        }

        return $response;
    }

    public function get(
        string $endpoint,
        array $params = [],
        array $headers = []
    ): string|bool {
        return $this->request('GET', $endpoint, $params, $headers);
    }

    public function post(
        string $endpoint,
        array $data = [],
        array $headers = []
    ): string|bool {
        return $this->request('POST', $endpoint, $data, $headers);
    }

    public function put(
        string $endpoint,
        array $data = [],
        array $headers = []
    ): string|bool {
        return $this->request('PUT', $endpoint, $data, $headers);
    }

    public function delete(
        string $endpoint,
        array $data = [],
        array $headers = []
    ): string|bool {
        return $this->request('DELETE', $endpoint, $data, $headers);
    }

    public function xmlResponse(string $response): ?array
    {
        // Attempt to parse the XML response
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            Basic::console("Failed to parse XML response.");
            return null; // or handle it as needed
        }

        // Convert XML to an associative array
        return json_decode(json_encode($xml), true);
    }

    public function close(): void
    {
        if ($this->ch) {
            curl_close($this->ch);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
