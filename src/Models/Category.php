<?php


namespace DooglysAdapter\Models;

use DooglysAdapter\Interfaces\IGetById;
use DooglysAdapter\Interfaces\IGetList;
use DooglysAdapter\Interfaces\IDelete;
use DooglysAdapter\Interfaces\ISave;
use DooglysAdapter\Models\Traits\GetList;
use DooglysAdapter\Models\Traits\GetById;
use DooglysAdapter\Models\Traits\Delete;
use DooglysAdapter\Models\Traits\Save;

class Category extends BaseEntity implements IGetById, IGetList, ISave, IDelete {

    use GetById, GetList, Save, Delete;

    const LIST_URI = '/api/v1/nomenclature/product-category/list';
    const UNIT_URI = '/api/v1/nomenclature/product-category/view/{id}';
    const CREATE_URI = '/api/v1/nomenclature/product-category/create';
    const UPDATE_URI = '/api/v1/nomenclature/product-category/update/{id}';
    const DELETE_URI = '/api/v1/nomenclature/product-category/delete/{id}';

}