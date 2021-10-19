<?php

namespace DooglysAdapter\Models\Traits;

trait GetList{

    public function getList( array $params = [] ) : array {

        $entityList = [];

        $results = $this->connector->send( self::LIST_URI, 'GET', $params );

        foreach ($results as $result):
            $object = new self($this->connector, $this->generator);
            $entityList[] = $object->setParams($result);
        endforeach;

        return $entityList;
    }

}