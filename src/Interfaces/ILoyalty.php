<?php

namespace DooglysAdapter\Interfaces;

interface ILoyalty{

    public function buyNew( array $options ) : array;

    public function buyCommit( array $options ) : array;

    public function buyReturn( array $options ) : array;

    public function getLoyaltySettings() : array;

    public function getCardInfo( array $options ) : array;

}