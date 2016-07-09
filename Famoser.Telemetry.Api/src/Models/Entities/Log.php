<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 09.07.2016
 * Time: 12:36
 */

namespace Famoser\MassPass\Models\Entities;


use Famoser\MassPass\Models\Entities\Base\BaseEntity;

class Log extends BaseEntity
{
    public $user_guid;
    public $message;
    public $log_level;
    public $location;
    public $create_date;
    public $handled;

    public function getTableName()
    {
        return "logs";
    }
}