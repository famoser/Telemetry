<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 09.07.2016
 * Time: 14:50
 */

namespace Famoser\MassPass\Models\ViewModels;


use Famoser\MassPass\Models\Entities\Application;
use Famoser\MassPass\Models\ViewModels\Base\BaseViewModel;

class ApplicationViewModel extends BaseViewModel
{
    /* @var Application */
    private $application;
    private $unhandledLogsCount;
    private $activityCount;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function getId()
    {
        return $this->application->id;
    }

    public function getName()
    {
        return $this->application->name;
    }

    public function getDescription()
    {
        return $this->application->description;
    }

    public function getProjectUrl()
    {
        return $this->application->projectUrl;
    }

    public function getUnhandledLogs()
    {
        return $this->application->description;
    }

    /**
     * @return int
     */
    public function getUnhandledLogsCount()
    {
        return $this->unhandledLogsCount;
    }

    /**
     * @param int $unhandledLogsCount
     */
    public function setUnhandledLogsCount($unhandledLogsCount)
    {
        $this->unhandledLogsCount = $unhandledLogsCount;
    }

    /**
     * @return int
     */
    public function getActivityCount()
    {
        return $this->activityCount;
    }

    /**
     * @param int $activityCount
     */
    public function setActivityCount($activityCount)
    {
        $this->activityCount = $activityCount;
    }
}