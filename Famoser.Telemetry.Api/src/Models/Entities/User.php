<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 09.07.2016
 * Time: 12:34
 */

namespace Famoser\MassPass\Models\Entities;


use Famoser\MassPass\Models\Entities\Base\BaseEntity;

class User extends BaseEntity
{
    public $guid;
    public $device_name;
    public $system_name;
    public $meta_data;
    public $application_id;
    public $create_date;

    public function getTableName()
    {
        return "users";
    }
}