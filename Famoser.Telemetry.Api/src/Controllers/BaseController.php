<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 14:23
 */

namespace Famoser\MassPass\Controllers;


use Famoser\MassPass\Helpers\DatabaseHelper;
use Famoser\MassPass\Helpers\LogHelper;
use Famoser\MassPass\Helpers\ResponseHelper;
use Famoser\MassPass\Models\Entities\Device;
use Famoser\MassPass\Models\Entities\User;
use Famoser\MassPass\Models\Request\Base\ApiRequest;
use Famoser\MassPass\Models\Response\Base\ApiResponse;
use Famoser\MassPass\Types\ApiErrorTypes;
use Interop\Container\ContainerInterface;
use Slim\Http\Response;

class BaseController
{
    protected $container;

    //Constructor
    public function __construct(ContainerInterface $ci)
    {
        $this->container = $ci;
    }

    protected function returnApiError($apiErrorType, Response $response, $debugMessage = null)
    {
        $apiError = array(
            ApiErrorTypes::DatabaseFailure => 500,
            ApiErrorTypes::ApiVersionInvalid => 406,
            ApiErrorTypes::Forbidden => 401,
            ApiErrorTypes::None => 200,
            ApiErrorTypes::NotWellDefined => 400,
            ApiErrorTypes::RequestFailure => 400,
            ApiErrorTypes::RequestUriInvalid => 404,
        );

        if (!in_array($apiErrorType, $apiError)) {
            $apiError[$apiErrorType] = 500;
        }

        $resp = new ApiResponse(false, $apiErrorType);
        $resp->DebugMessage = $debugMessage;

        return $response->withStatus($apiError[$apiErrorType])->withJson($resp);
    }

    protected function returnApiSuccess(Response $response, $debugMessage = null)
    {
        $resp = new ApiResponse();
        $resp->DebugMessage = $debugMessage;

        return $response->withStatus(200)->withJson($resp);
    }

    protected function isWellDefined($postArray, $neededProps)
    {
        if ($neededProps != null)
            foreach ($neededProps as $neededProp) {
                if (!isset($postArray[$neededProp])) {
                    LogHelper::log("not a property: " . $neededProp . " in request " . json_encode($postArray, JSON_PRETTY_PRINT), "isWellDefined_" . uniqid() . ".txt");
                    return false;
                }
            }
        return true;
    }

    private $databaseHelper;

    protected function getDatabaseHelper()
    {
        if ($this->databaseHelper == null)
            $this->databaseHelper = new DatabaseHelper($this->container);
        return $this->databaseHelper;
    }

    protected function renderTemplate(Response $response, $path, $args)
    {
        return $this->container->get("view")->render($response, $path . ".html.twig", $args);
    }
}