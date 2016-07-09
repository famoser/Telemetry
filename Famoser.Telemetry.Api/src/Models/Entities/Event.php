<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 09.07.2016
 * Time: 12:35
 */

namespace Famoser\MassPass\Models\Entities;


use Famoser\MassPass\Models\Entities\Base\BaseEntity;

class Event extends BaseEntity
{
    public $user_guid;
    public $event_id;
    public $create_date;

    public function getTableName()
    {
        return "events";
    }
}