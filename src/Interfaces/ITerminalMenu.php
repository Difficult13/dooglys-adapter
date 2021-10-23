<?php

namespace DooglysAdapter\Interfaces;

interface ITerminalMenu{

    public function getMenuKit( string $id ) : array;

    public function getMenu( string $id ) : array;

    public function getModifier( string $id ) : array;

    public function getKitProducts( string $id ) : array;

}