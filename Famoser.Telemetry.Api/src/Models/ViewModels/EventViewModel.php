<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 09.07.2016
 * Time: 14:30
 */

namespace Famoser\MassPass\Models\ViewModels;


use Famoser\MassPass\Models\Entities\Event;
use Famoser\MassPass\Models\ViewModels\Base\BaseViewModel;

class EventViewModel extends BaseViewModel
{
    /* @var Event */
    private $event;
    private $userViewModel;

    public function __construct(Event $event, UserViewModel $userViewModel)
    {
        $this->event = $event;
        $this->userViewModel = $userViewModel;
    }

    public function getUserId()
    {
        return $this->userViewModel->getId();
    }

    public function getUser()
    {
        return $this->userViewModel->getName();
    }

    public function getDate()
    {
        return $this->formatDateTime($this->event->create_date);
    }

    public function getName()
    {
        return $this->event->event_id;
    }
}