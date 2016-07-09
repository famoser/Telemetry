<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 09.07.2016
 * Time: 14:43
 */

namespace Famoser\MassPass\Models\ViewModels;


use Famoser\MassPass\Models\Entities\User;
use Famoser\MassPass\Models\ViewModels\Base\BaseViewModel;

class UserViewModel extends BaseViewModel
{
    /* @var User */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getId()
    {
        return $this->user->id;
    }

    public function getName()
    {
        return substr($this->user->device_name, 0, 12) . " (" . substr($this->user->guid, 0, 5) . ")";
    }

    public function getDate()
    {
        return $this->formatDateTime($this->user->create_date);
    }

    public function getDeviceName()
    {
        return $this->user->device_name;
    }

    public function getMetaData()
    {
        return $this->user->meta_data;
    }

    public function getSystemName()
    {
        return $this->user->system_name;
    }
}