<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 09.07.2016
 * Time: 14:52
 */

namespace Famoser\MassPass\Models\ViewModels;


use Famoser\MassPass\Models\Entities\Log;
use Famoser\MassPass\Models\ViewModels\Base\BaseViewModel;
use Famoser\MassPass\Types\LogLevelType;

class LogViewModel extends BaseViewModel
{
    /* @var Log */
    private $log;
    private $userViewModel;

    public function __construct(Log $log, UserViewModel $userViewModel)
    {
        $this->log = $log;
        $this->userViewModel = $userViewModel;
    }

    public function getId()
    {
        return $this->log->id;
    }

    public function getUserId()
    {
        return $this->userViewModel->getId();
    }

    public function getUser()
    {
        return $this->userViewModel->getName();
    }

    public function getLogType()
    {
        switch ($this->log->log_level) {
            case LogLevelType::Error:
                return "Error";
            case LogLevelType::FatalError:
                return "FatalError";
            case LogLevelType::Info:
                return "Info";
            case LogLevelType::Warning:
                return "Warning";
            case LogLevelType::WtfAreYouDoingError:
                return "WtfAreYouDoingError";
            default:
                return "unknown";
        }
    }

    public function getDate()
    {
        return $this->formatDateTime($this->log->create_date);
    }

    public function getMessage()
    {
        return $this->log->message;
    }

    public function getLocation()
    {
        return $this->log->location;
    }
}