<?php

namespace DooglysAdapter\Interfaces;

interface IGetById{

    public function getById( string $externalId ) : IGetById;

}