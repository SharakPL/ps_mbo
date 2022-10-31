<?php

namespace PrestaShop\Module\Mbo\Helpers;

/**
 * The purpose of this class is to provide a way to make asynchronous HTTP requests.
 * GuzzleHttp\Client::requestAsync() is not used because it does not allow "Fire and Forget" requests.
 */
class AsyncClient
{
    public const METHOD_POST = 'POST';

    public const METHOD_GET = 'GET';

    /**
     * Process an async request using the socket connection
     *
     * @param string $url
     * @param array $params
     * @param array $customHeaders
     * @param string $method
     *
     * @return bool
     */
    public static function request(string $url, array $params = [], array $customHeaders = [], string $method = self::METHOD_POST): bool
    {
        $endpointParts = parse_url($url);
        $endpointParts['path'] = $endpointParts['path'] ?? '/';
        $endpointParts['port'] = $endpointParts['port'] ?? ($endpointParts['scheme'] === 'https' ? 443 : 80);
        $socket = self::openSocket($endpointParts['host'], $endpointParts['port']);

        if (!$socket) {
            return false;
        }

        if ($method === self::METHOD_GET) {
            return self::get($endpointParts, $socket, $customHeaders);
        }

        return self::post($endpointParts, $socket, $params, $customHeaders);
    }

    private static function get(array $endpointParts, $socket, array $customHeaders = []): bool
    {
        if (!empty($endpointParts['query'])) {
            $contentLength = strlen($endpointParts['query']);
            $endpointParts['path'] .= '?' . $endpointParts['query'];
        }
        $request = "GET {$endpointParts['path']} HTTP/1.1\r\n";
        $request .= "Host: {$endpointParts['host']}\r\n";
        foreach ($customHeaders as $header) {
            $request .= "{$header}\r\n";
        }
        $request .= "Content-Type:application/x-www-form-urlencoded\r\n";
        if (isset($contentLength)) {
            $request .= "Content-Length: {$contentLength}\r\n";
        }
        $request .= "Connection:Close\r\n\r\n";

        fwrite($socket, $request);
        fclose($socket);

        return true;
    }

    private static function post(array $endpointParts, $socket, array $postData = [], array $customHeaders = []): bool
    {
        $encodedPostData = json_encode($postData);
        $contentLength = strlen($encodedPostData);

        $request = "POST {$endpointParts['path']} HTTP/1.1\r\n";
        $request .= "Host: {$endpointParts['host']}\r\n";
        foreach ($customHeaders as $header) {
            $request .= "{$header}\r\n";
        }
        $request .= "Content-Type: application/json\r\n\r\n";
        $request .= "Content-Length: {$contentLength}\r\n";
        $request .= $encodedPostData;
        $request .= "Connection:Close\r\n\r\n";

        fwrite($socket, $request);
        fclose($socket);

        return true;
    }

    private static function openSocket(string $host, int $port)
    {
        try {
            return fsockopen($host, $port);
        } catch (\Exception $e) {
            return false;
        }
    }
}
