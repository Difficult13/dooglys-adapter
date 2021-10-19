<?php

namespace DooglysAdapter\Models\Traits;

use DooglysAdapter\Interfaces\IGetById;

trait GetById{

    public function getById( string $externalId ) : IGetById {

        $uri = str_replace('{id}', $externalId, self::UNIT_URI);

        $result = $this->connector->send( $uri, 'GET' );

        return $this->setParams($result);
    }

}