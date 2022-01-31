<?php

namespace DooglysAdapter;

use DooglysAdapter\Exceptions\NotExistClassException;
use DooglysAdapter\Interfaces\IConnector;
use DooglysAdapter\Interfaces\IBaseEntity;
use DooglysAdapter\Interfaces\ILoyalty;
use DooglysAdapter\Interfaces\IStructure;
use DooglysAdapter\Interfaces\ITerminalMenu;
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

class DooglysAdapter implements IStructure, ITerminalMenu, ILoyalty {

    const SETTING_URI = '/api/v1/structure/tenant/settings';

    const TERMINAL_MENU_KIT_URI = '/api/v1/terminal-menu/menu/kit/{id}';
    const TERMINAL_MENU_URI = '/api/v1/terminal-menu/menu/view/{id}';
    const TERMINAL_MENU_MODIFIER_URI = '/api/v1/terminal-menu/menu/modifier/{id}';
    const TERMINAL_MENU_KIT_PRODUCTS_URI = '/api/v1/terminal-menu/menu/kit-products/{id}';

    const LOYALTY_BUY_NEW_URI = '/api/v1/loyalty/transaction/buy-new';
    const LOYALTY_BUY_COMMIT_URI = '/api/v1/loyalty/transaction/buy-commit';
    const LOYALTY_RETURN_URI = '/api/v1/loyalty/transaction/buy-return';
    const LOYALTY_SETTING_URI = '/api/v1/loyalty/settings/view';
    const LOYALTY_CARD_INFO_URI = '/api/v1/loyalty/card/info';

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
            throw new \BadMethodCallException("Dooglys-adapter error: Call to undefined method {$name}");

        return $this->build($name);
    }

    public function build( string $entity ) : IBaseEntity {
        $classEntity = $this->getClassName($entity);

        if (!class_exists( $classEntity ))
            throw new NotExistClassException("Dooglys-adapter error: Attempt to access a non-existent class {$classEntity}");

        $entityObject = new $classEntity($this->connector, $this->generator);

        return $entityObject;
    }

    public function getStructureSettings() : array{
        return $this->connector->send(self::SETTING_URI, 'GET');
    }

    public function getMenuKit( string $id ) : array{
        $uri = str_replace('{id}', $id, self::TERMINAL_MENU_KIT_URI);
        return $this->connector->send($uri, 'GET');
    }

    public function getMenu( string $id ) : array{
        $uri = str_replace('{id}', $id, self::TERMINAL_MENU_URI);
        return $this->connector->send($uri, 'GET');
    }

    public function getModifier( string $id ) : array{
        $uri = str_replace('{id}', $id, self::TERMINAL_MENU_MODIFIER_URI);
        return $this->connector->send($uri, 'GET');
    }

    public function getKitProducts( string $id ) : array{
        $uri = str_replace('{id}', $id, self::TERMINAL_MENU_KIT_PRODUCTS_URI);
        return $this->connector->send($uri, 'GET');
    }

    public function buyNew( array $options ) : array{
        return $this->connector->send(self::LOYALTY_BUY_NEW_URI, 'POST', $options);
    }

    public function buyCommit( array $options ) : array{
        return $this->connector->send(self::LOYALTY_BUY_COMMIT_URI, 'POST', $options);
    }

    public function buyReturn( array $options ) : array{
        return $this->connector->send(self::LOYALTY_RETURN_URI, 'POST', $options);
    }

    public function getLoyaltySettings() : array{
        return $this->connector->send(self::LOYALTY_SETTING_URI, 'GET');
    }

    public function getCardInfo( array $options ) : array{
        return $this->connector->send(self::LOYALTY_CARD_INFO_URI, 'POST', $options);
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