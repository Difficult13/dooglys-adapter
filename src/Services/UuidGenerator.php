<?php
namespace DooglysAdapter\Services;

use DooglysAdapter\Interfaces\IUuidGenerator;
use Ramsey\Uuid\Uuid;

class UuidGenerator implements IUuidGenerator {
    public function generate() : string{
        $uuid = Uuid::uuid4();
        return $uuid->toString();
    }
}