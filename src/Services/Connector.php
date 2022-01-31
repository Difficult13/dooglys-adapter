<?php

namespace DooglysAdapter\Services;

use DooglysAdapter\Interfaces\IConnector;
use GuzzleHttp\Client;
use DooglysAdapter\Exceptions\RequestException;

class Connector implements IConnector {

    const DOOGLYS_BASE_ADDRESS = "https://{domain}.dooglys.com";

    private string $accessToken;
    private string $domain;
    private string $baseUri;
    private Client $client;

    private $timeout = 10;

    public function __construct( string $accessToken, string $domain ){
        $this->accessToken = $accessToken;
        $this->domain = $domain;

        $this->setBaseUri();
        $this->setClient();
    }

    public function send( string $uri, string $method, array $params = [] ): array{
        $params = $this->prepareParams($params, $method);

        try {
            $response = $this->client->request($method, $uri, $params);
        } catch ( \GuzzleHttp\Exception\RequestException $guzzleException ) {

            if ($guzzleException->getResponse() !== null)
                throw new RequestException(
                    'Dooglys-adapter error: Request failed - ' . $guzzleException->getResponse()->getBody()->getContents(), $guzzleException->getCode(), $guzzleException
                );

            throw $guzzleException;
        }

        $result = (string) $response->getBody();
        $result = $this->responseDecode($result);
        return $result;
    }

    private function setBaseUri(){
        $this->baseUri = str_replace('{domain}', $this->domain, self::DOOGLYS_BASE_ADDRESS);
    }

    private function setClient(){
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => $this->timeout,
            'User-Agent' => 'dooglys-adapter',
            'headers' => [
                'Access-Token' => $this->accessToken,
                'Tenant-Domain' => $this->domain,
            ],
        ]);
    }

    private function prepareParams( array $params, string $method ) : array{

        if ( $method === 'GET' ):
            $params = [
                'query' => $params
            ];
        else:
            $params = [
                'json' => $params
            ];
        endif;

        return $params;
    }

    private function responseDecode( string $data ) : array{
        return json_decode($data, true) ?? [];
    }
}