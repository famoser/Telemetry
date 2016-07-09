<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 09.07.2016
 * Time: 12:23
 */

namespace Famoser\MassPass\Controllers;


use Famoser\MassPass\Models\Entities\Event;
use Famoser\MassPass\Models\Entities\Log;
use Famoser\MassPass\Models\Entities\User;
use Famoser\MassPass\Types\ApiErrorTypes;
use Famoser\MassPass\Types\ContentTypes;
use Slim\Http\Request;
use Slim\Http\Response;

class SubmitController extends BaseController
{
    public function submit(Request $request, Response $response, $args)
    {
        if (!$this->isWellDefined($_POST, array("UserId", "ApplicationId", "Version", "ContentType")))
            return $this->returnApiError(ApiErrorTypes::NotWellDefined, $response);
        $userId = $_POST["UserId"];
        $applicationId = $_POST["ApplicationId"];
        $version = $_POST["Version"];
        $contentType = $_POST["ContentType"];

        if ($contentType == ContentTypes::UserInfoContentType) {
            if (!$this->isWellDefined($_POST, array("DeviceName", "SystemName", "MetaData")))
                return $this->returnApiError(ApiErrorTypes::NotWellDefined, $response);

            $user = $this->getDatabaseHelper()->getSingleFromDatabase(new User(), "guid = :guid", array("guid" => $userId));
            if ($user == null) {
                $user = new User();
                $user->create_date = time();
                $user->guid = $userId;
            }
            $user->device_name = $_POST["DeviceName"];
            $user->system_name = $_POST["SystemName"];
            $user->meta_data = $_POST["MetaData"];
            $user->application_id = $applicationId;
            if (!$this->getDatabaseHelper()->saveToDatabase($user))
                return $this->returnApiError(ApiErrorTypes::DatabaseFailure, $response);

            return $this->returnApiSuccess($response);
        } else if ($contentType == ContentTypes::EventContentType) {
            if (!$this->isWellDefined($_POST, array("Event")))
                return $this->returnApiError(ApiErrorTypes::NotWellDefined, $response);
            $event = new Event();
            $event->create_date = time();
            $event->user_guid = $userId;
            $event->event_id = $_POST["Event"];
            if (!$this->getDatabaseHelper()->saveToDatabase($event))
                return $this->returnApiError(ApiErrorTypes::DatabaseFailure, $response);

            return $this->returnApiSuccess($response);
        } else if ($contentType == ContentTypes::LogModelContentType) {

            if (!$this->isWellDefined($_POST, array("Message", "LogLevel", "Location")))
                return $this->returnApiError(ApiErrorTypes::NotWellDefined, $response);

            $log = new Log();
            $log->create_date = time();
            $log->user_guid = $userId;
            $log->location = $_POST["Location"];
            $log->log_level = $_POST["LogLevel"];
            $log->message = $_POST["Message"];
            if (!$this->getDatabaseHelper()->saveToDatabase($log))
                return $this->returnApiError(ApiErrorTypes::DatabaseFailure, $response);

            return $this->returnApiSuccess($response);
        }
        return $this->returnApiError(ApiErrorTypes::UnknownContentType, $response);
    }
}