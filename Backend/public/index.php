<?php

declare(strict_types=1);

use App\Controllers\ConsumptionController;
use App\Controllers\DrinkController;
use App\Middlewares\CorsMiddleware;
use App\Middlewares\EcoMetricsMiddleware;
use App\Responses\ProblemDetailsResponse;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Paris');

$container = (new ContainerBuilder())
    ->addDefinitions([

        PDO::class => function (): PDO {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                getenv('DB_HOST') ?: '127.0.0.1',
                getenv('DB_PORT') ?: '3306',
                getenv('DB_NAME') ?: 'devcoffee'
            );

            return new PDO($dsn, getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        },

        LoggerInterface::class => function (): LoggerInterface {
            $logger = new Logger('devcoffee');
            $logger->pushHandler(new StreamHandler(__DIR__ . '/../var/logs/app.log', Level::Debug));

            return $logger;
        },

        ResponseFactoryInterface::class => \DI\create(ResponseFactory::class),
    ])
    ->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$logger = $container->get(LoggerInterface::class);

$app->addRoutingMiddleware();

$app->addBodyParsingMiddleware([
    'application/json' => static function (string $input): ?array {
        $decoded = json_decode($input, true);

        return is_array($decoded) ? $decoded : null;
    },
]);

$errorMiddleware = $app->addErrorMiddleware(false, false, false);
$errorMiddleware->setDefaultErrorHandler(
    function (ServerRequestInterface $request, Throwable $exception) use ($logger) {
        $problem = ProblemDetailsResponse::fromThrowable($exception);
        $status  = $problem->getStatusCode();

        $context = [
            'method' => $request->getMethod(),
            'path'   => $request->getUri()->getPath(),
            'status' => $status,
        ];

        if ($status >= 500) {
            $logger->error($exception->getMessage(), $context + ['exception' => $exception]);
        } else {
            $logger->warning($exception->getMessage(), $context);
        }

        return $problem;
    }
);

$app->add(CorsMiddleware::class);        
$app->add(EcoMetricsMiddleware::class);

$app->group('/api', function (RouteCollectorProxy $group): void {

    $group->get('/drinks', [DrinkController::class, 'index']);
    $group->get('/drinks/{id:[0-9]+}', [DrinkController::class, 'show']);
    $group->post('/drinks', [DrinkController::class, 'create']);

    $group->get('/consumptions', [ConsumptionController::class, 'index']);
    $group->post('/consumptions', [ConsumptionController::class, 'create']);
    $group->delete('/consumptions/{id:[0-9]+}', [ConsumptionController::class, 'delete']);
});

$app->run();
