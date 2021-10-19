<?php

namespace DooglysAdapter\Interfaces;

interface IConnector{

    public function send( string $uri, string $method, array $params = [] ) : array;

}