<?php

namespace DooglysAdapter;

use DooglysAdapter\Exceptions\NotExistClassException;
use DooglysAdapter\Interfaces\IConnector;
use DooglysAdapter\Interfaces\IBaseEntity;
use DooglysAdapter\Interfaces\IUuidGenerator;
use DooglysAdapter\Services\Connector;
use DooglysAdapter\Services\UuidGenerator;

/**
 * @method product() : IBaseEntity
 * @method category() : IBaseEntity
 * @method order() : IBaseEntity
 * @method salepoint() : IBaseEntity
 * @method user() : IBaseEntity
 * @method warehouse() : IBaseEntity
 */

class DooglysAdapter{

    const SETTING_URI = '/api/v1/structure/tenant/settings';

    private string $accessToken;
    private string $domain;

    private IConnector $connector;
    private IUuidGenerator $generator;

    public function __construct( string $accessToken, string $domain ){
        $this->accessToken = $accessToken;
        $this->domain = $domain;

        $this->initConnector();
        $this->initUuidGenerator();
    }

    public function __call( string $name, array $arguments = [] ){
        $classEntity = $this->getClassName($name);
        if ( $arguments || !class_exists( $classEntity ) )
            throw new \BadMethodCallException("Call to undefined method {$name}");

        return $this->build($name);
    }

    public function build( string $entity ) : IBaseEntity {
        $classEntity = $this->getClassName($entity);

        if (!class_exists( $classEntity ))
            throw new NotExistClassException("Attempt to access a non-existent class {$classEntity}");

        $entityObject = new $classEntity($this->connector, $this->generator);

        return $entityObject;
    }

    public function getSettings() : array{
        return $this->connector->send(self::SETTING_URI, 'GET');
    }

    private function initConnector(){
        $this->connector = new Connector($this->accessToken, $this->domain);
    }

    private function initUuidGenerator(){
        $this->generator = new UuidGenerator();
    }

    private function getClassName( $entity ) : string {
        return '\\DooglysAdapter\\Models\\' . ucfirst($entity);
    }


}