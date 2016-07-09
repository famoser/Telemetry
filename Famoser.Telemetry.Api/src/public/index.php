<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/05/2016
 * Time: 22:40
 */

use Famoser\MassPass\Helpers\DatabaseHelper;
use Famoser\MassPass\Helpers\RequestHelper;
use Famoser\MassPass\Helpers\ResponseHelper;
use Famoser\MassPass\Middleware\ApiVersionMiddleware;
use Famoser\MassPass\Middleware\AuthorizationMiddleware;
use Famoser\MassPass\Middleware\JsonMiddleware;
use Famoser\MassPass\Middleware\LoggingMiddleware;
use Famoser\MassPass\Middleware\TestsMiddleware;
use Famoser\MassPass\Models\Request\Base\ApiRequest;
use Famoser\MassPass\Models\Request\RefreshRequest;
use Famoser\MassPass\Models\Response\Base\ApiResponse;
use Famoser\MassPass\Types\ApiErrorTypes;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use Slim\Container;

require '../../vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => false,
        'debug_mode' => true,
        'db' => [
            'path' => "sqlite.db",
            'test_path' => "sqlite_tests.db"
        ],
        'data_path' => realpath("../../app"),
        'asset_path' => realpath("../Assets"),
        'log_path' => realpath("../../app/logs"),
        'file_path' => realpath("../../app/files"),
        'template_path' => realpath("../../app/templates"),
        'cache_path' => realpath("../../app/cache"),
        'public_path' => realpath("../public")
    ],
    'api_settings' => [
        'api_version' => 1,
        'test_mode' => false
    ]
];

$c = new Container($configuration);
$c['notFoundHandler'] = function (Container $c) {
    return function (Request $req, Response $resp) use ($c) {
        $res = new ApiResponse(false, ApiErrorTypes::RequestUriInvalid);
        if ($c->get("settings")["debug_mode"])
            $res->DebugMessage = "requested: " . $req->getRequestTarget();

        return $resp->withStatus(404, "endpoint not found")->withJson($res);
    };
};
$c['notAllowedHandler'] = function (Container $c) {
    return function (Request $req, Response $resp) use ($c) {
        $res = new ApiResponse(false, ApiErrorTypes::RequestUriInvalid);
        if ($c->get("settings")["debug_mode"])
            $res->DebugMessage = "requested: " . $req->getRequestTarget();

        return $resp->withStatus(405, "wrong method")->withJson($res);
    };
};
$c['errorHandler'] = function (Container $c) {
    /**
     * @param $request
     * @param $response
     * @param $exception
     * @return mixed
     */
    return function (Request $request, Response $response, Exception $exception) use ($c) {
        $res = new ApiResponse(false, ApiErrorTypes::ServerFailure);
        if ($c->get("settings")["debug_mode"])
            $res->DebugMessage = "Exception: " . $exception->getMessage() . " \nStack: " . $exception->getTraceAsString();
        return $response->withStatus(500, $exception->getMessage())->withJson($res);
    };
};
// Register component on container
$c['view'] = function (Container $c) {
    $view = new \Slim\Views\Twig($c->get("settings")["template_path"], [
        'cache' => $c->get("settings")["cache_path"],
        'debug' => $c->get("settings")["debug_mode"]
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $c['router'],
        $c['request']->getUri()
    ));

    return $view;
};

$controllerNamespace = 'Famoser\MassPass\Controllers\\';

$app = new App($c);
$app->add(new ApiVersionMiddleware($c));
$app->add(new LoggingMiddleware($c));

$routes = function () use ($controllerNamespace) {
    $this->post('/submit', $controllerNamespace . 'SubmitController:submit')->setName("submit");
    $this->get('/', $controllerNamespace . 'PublicController:index')->setName("index");
    $this->get('/view/{id}', $controllerNamespace . 'PublicController:application')->setName("application_view");
    $this->get('/user/{id}', $controllerNamespace . 'PublicController:user')->setName("user_view");
    $this->get('/log/{id}', $controllerNamespace . 'PublicController:log')->setName("log_view");
};

$app->group("/1.0", $routes);




$app->run();