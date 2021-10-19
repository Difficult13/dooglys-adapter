<?php

namespace DooglysAdapter\Services;

use DooglysAdapter\Interfaces\IConnector;
use GuzzleHttp\Client;

class Connector implements IConnector {

    const DOOGLYS_BASE_ADDRESS = "https://{domain}.dooglys.com";

    private string $accessToken;
    private string $domain;
    private string $baseUri;
    private Client $client;

    public function __construct( string $accessToken, string $domain ){
        $this->accessToken = $accessToken;
        $this->domain = $domain;

        $this->setBaseUri();
        $this->setClient();
    }

    public function send( string $uri, string $method, array $params = [] ): array{

        $params = $this->prepareParams($params, $method);

        $response = $this->client->request($method, $uri, $params);

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
            'headers' => [
                'Access-Token' => $this->accessToken,
                'Tenant-Domain' => $this->domain,
            ],
            'timeout' => 10
        ]);
    }

    private function prepareParams( array $params, string $method ) : array{

        if ( $method === 'GET' ):
            $params = [
                'query' => $params
            ];
        else:
            $params = [
                'form_params' => $params
            ];
        endif;

        return $params;
    }

    private function responseDecode( string $data ){
        return json_decode($data, true) ?? [];
    }
}