<?php

namespace DooglysAdapter\Models;

use DooglysAdapter\Interfaces\IGetById;
use DooglysAdapter\Interfaces\IGetList;
use DooglysAdapter\Models\Traits\GetList;
use DooglysAdapter\Models\Traits\GetById;

/**
 * @method  save()
 * @method  delete()
 */
class Warehouse extends BaseEntity implements IGetById, IGetList {

    use GetById, GetList;

    const LIST_URI = '/api/v1/warehouse/document/list';
    const UNIT_URI = '/api/v1/warehouse/document/view/{id}';
}