<?php

namespace DooglysAdapter\Interfaces;

/**
 * @method getById( string $externalId )
 * @method getList( array $params = [] )
 * @method save()
 * @method delete()
 */

interface IBaseEntity{

    public function getParams() : array;

    public function getParam( string $key );

    public function __get( string $key );

    public function setParams( array $params ) : IBaseEntity;

    public function setParam( string $key, $value ) : IBaseEntity;

    public function __set( string $key, $value ) : void;

    public function clearParams() : IBaseEntity;

    public function deleteParam( string $key ) : IBaseEntity;

}