<?php

namespace DooglysAdapter\Models\Traits;

use DooglysAdapter\Interfaces\ISave;

trait Save{

    public function save() : ISave{
        if ( !$this->id ):
            $this->createEntity();
        else:
            $this->saveEntity();
        endif;

        return $this;
    }

    private function createEntity(){
        $uri = self::CREATE_URI;
        $this->id = $this->generator->generate();
        $result = $this->connector->send($uri, 'POST', $this->getParams());
        $this->setParams($result);
    }

    private function saveEntity(){
        $uri = str_replace('{id}', $this->id, self::UPDATE_URI);
        $result = $this->connector->send($uri, 'POST', $this->getParams());
        $this->setParams($result);
    }

}