<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 09.07.2016
 * Time: 12:33
 */

namespace Famoser\MassPass\Models\Entities;


use Famoser\MassPass\Models\Entities\Base\BaseEntity;

class Application extends BaseEntity
{
    public $guid;
    public $name;
    public $description;
    public $projectUrl;

    public function getTableName()
    {
        return "applications";
    }
}