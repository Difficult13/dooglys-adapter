<?php

namespace DooglysAdapter\Models;

use DooglysAdapter\Interfaces\IConnector;
use DooglysAdapter\Interfaces\IBaseEntity;
use DooglysAdapter\Interfaces\IUuidGenerator;

abstract class BaseEntity implements IBaseEntity {

    protected IConnector $connector;
    protected IUuidGenerator $generator;

    protected array $params;

    public function __construct( IConnector $connector, IUuidGenerator $generator){

        $this->connector = $connector;
        $this->generator = $generator;

        return $this;
    }

    public function getParams() : array{
        return $this->params;
    }

    public function getParam( string $key ){
        return $this->$key;
    }

    public function __get( string $key ){
        return $this->params[$key] ?? null;
    }

    public function __set( string $key, $value) : void {
        $this->params[$key] = $value;
    }

    public function setParams( array $params ) : IBaseEntity {
        $this->params = $params;
        return $this;
    }

    public function setParam( string $key, $value ) : IBaseEntity{
        $this->$key = $value;
        return $this;
    }

    public function clearParams() : IBaseEntity {
        $this->params = [];
        return $this;
    }

    public function deleteParam( string $key ) : IBaseEntity {
        unset($this->params[$key]);
        return $this;
    }
}