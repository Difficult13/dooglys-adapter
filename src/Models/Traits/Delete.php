<?php

namespace DooglysAdapter\Models\Traits;

use DooglysAdapter\Exceptions\DeleteEntityException;
use DooglysAdapter\Exceptions\EmptyElementException;

trait Delete{
    public function delete() : void{
        if ( !$this->id )
            throw new EmptyElementException('Attempt to delete an empty element');

        $uri = str_replace('{id}', $this->id, self::DELETE_URI);
        $result = $this->connector->send( $uri, 'POST' );

        if ( !is_array($result) )
            throw new DeleteEntityException('Error when deleting an entity of the type - ' . __CLASS__);

        $this->clearParams();
    }
}