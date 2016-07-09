<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/06/2016
 * Time: 17:54
 */

namespace Famoser\MassPass\Controllers;


use Famoser\MassPass\Models\ApiConfiguration;
use Famoser\MassPass\Models\Entities\Application;
use Famoser\MassPass\Models\Entities\Event;
use Famoser\MassPass\Models\Entities\Log;
use Famoser\MassPass\Models\Entities\User;
use Famoser\MassPass\Models\ViewModels\ApplicationViewModel;
use Famoser\MassPass\Models\ViewModels\EventViewModel;
use Famoser\MassPass\Models\ViewModels\LogViewModel;
use Famoser\MassPass\Models\ViewModels\UserViewModel;
use PHPQRCode\Constants;
use PHPQRCode\QRcode;
use Slim\Http\Request;
use Slim\Http\Response;

class PublicController extends BaseController
{
    public function index(Request $request, Response $response, $args)
    {
        $viewArgs = array();
        $application = $this->getDatabaseHelper()->getFromDatabase(new Application(), null, null, "name");
        $applicationViewModels = array();
        foreach ($application as $item) {
            if ($item->id != 0)
                $applicationViewModels[] = new ApplicationViewModel($item);
        }
        $viewArgs["applications"] = $applicationViewModels;

        return $this->renderTemplate($response, "index", $viewArgs);
    }

    public function application(Request $request, Response $response, $args)
    {
        $viewArgs = array();
        $application = $this->getDatabaseHelper()->getSingleFromDatabase(new Application(), "id = :id", array("id" => $args["id"]));
        $viewArgs["application"] = new ApplicationViewModel($application);

        $users = $this->getDatabaseHelper()->getFromDatabase(new User(), "application_id = :application_id", array("application_id" => $application->guid));
        $userViewModels = array();
        $guids = array();
        foreach ($users as $user) {
            $userViewModels[$user->guid] = new UserViewModel($user);
            $guids[] = $user->guid;
        }
        $viewArgs["users"] = $userViewModels;

        $events = $this->getDatabaseHelper()->getWithInFromDatabase(new Event(), "user_guid", $guids, false, null, null, "create_date DESC", 20);
        $eventViewModels = array();
        foreach ($events as $event) {
            $eventViewModels[] = new EventViewModel($event, $userViewModels[$event->user_guid]);
        }
        $viewArgs["events"] = $eventViewModels;

        $logs = $this->getDatabaseHelper()->getWithInFromDatabase(new Log(), "user_guid", $guids, false, null, null, "create_date DESC", 20);
        $logViewModels = array();
        foreach ($logs as $log) {
            $logViewModels[] = new LogViewModel($log, $userViewModels[$log->user_guid]);
        }
        $viewArgs["logs"] = $logViewModels;

        return $this->renderTemplate($response, "application", $viewArgs);
    }

    public function log(Request $request, Response $response, $args)
    {
        $viewArgs = array();
        $log = $this->getDatabaseHelper()->getSingleFromDatabase(new Log(), "id = :id", array("id" => $args["id"]));
        $user = $this->getDatabaseHelper()->getSingleFromDatabase(new User(), "guid = :guid", array("guid" => $log->user_guid));
        $application = $this->getDatabaseHelper()->getSingleFromDatabase(new Application(), "guid = :guid", array("guid" => $user->application_id));
        $userViewModel = new UserViewModel($user);
        $viewArgs["log"] = new LogViewModel($log, $userViewModel);
        $viewArgs["application"] = new ApplicationViewModel($application);
        $viewArgs["user"] = $userViewModel;
        return $this->renderTemplate($response, "log", $viewArgs);
    }

    public function user(Request $request, Response $response, $args)
    {
        $viewArgs = array();
        $user = $this->getDatabaseHelper()->getSingleFromDatabase(new User(), "id = :id", array("id" => $args["id"]));
        $viewArgs["user"] = new UserViewModel($user);

        $application = $this->getDatabaseHelper()->getSingleFromDatabase(new Application(), "guid = :application_id", array("application_id" => $user->application_id));
        $viewArgs["application"] = new ApplicationViewModel($application);

        $events = $this->getDatabaseHelper()->getFromDatabase(new Event(), "user_guid = :user_guid", array("user_guid" => $user->guid), "create_date DESC", 20);
        $eventViewModels = array();
        foreach ($events as $event) {
            $eventViewModels[] = new EventViewModel($event, $viewArgs["user"]);
        }
        $viewArgs["events"] = $eventViewModels;

        $logs = $this->getDatabaseHelper()->getFromDatabase(new Log(), "user_guid = :user_guid", array("user_guid" => $user->guid), "create_date DESC", 20);
        $logViewModels = array();
        foreach ($logs as $log) {
            $logViewModels[] = new LogViewModel($log, $viewArgs["user"]);
        }
        $viewArgs["logs"] = $logViewModels;

        return $this->renderTemplate($response, "user", $viewArgs);
    }
}