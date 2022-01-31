<?php

namespace DooglysAdapter\Models\Traits;

use DooglysAdapter\Exceptions\EmptyElementException;

trait Delete{
    public function delete() : void{
        if ( !$this->id )
            throw new EmptyElementException('Dooglys-adapter error: Attempt to delete an empty element');

        $uri = str_replace('{id}', $this->id, self::DELETE_URI);
        $this->connector->send( $uri, 'POST' );

        $this->clearParams();
    }
}