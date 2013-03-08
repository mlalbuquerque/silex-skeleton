<?php

use Symfony\Component\HttpFoundation\Request as Request,
    Symfony\Component\HttpFoundation\Response as Response;

// Constants
define('APP_NAME', 'SILEX_SKELETON');
define('HOST', $_SERVER['HTTP_HOST']);
define('ROOT', __DIR__ . '/..');
define('WEBROOT', ROOT . '/web');
define('CONFIGROOT', ROOT . '/config');
// Attribute name used in log
define('USERNAME_METHOD_LOGGED', '__toString');

// New libs bootstraping and register
$loader = require_once __DIR__.'/../vendor/autoload.php';

// Starting the engines and debugging
$app = new Silex\Application();
$app['debug'] = true;
date_default_timezone_set('America/Sao_Paulo'); // Your default Timezone

// Doctrine register
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => require_once ROOT . '/config/database.php'
));

// Twig register
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => ROOT . '/views'
));

// Monolog register
$app->register(new Log\LoggerServiceProvider(), array(
    'monolog.name'    => APP_NAME,
    'monolog.level'   => $app['debug'] ? \Monolog\Logger::DEBUG : \Monolog\Logger::INFO,
    'monolog.logfile' => ROOT . '/log/silex.log',
    'monolog.maxfiles' => 30,
    'monolog.handler' => function () use ($app) {
        // For other classes see https://github.com/Seldaek/monolog#handlers
        return new Monolog\Handler\RotatingFileHandler($app['monolog.logfile'], $app['monolog.maxfiles'], $app['monolog.level']);
    }
));

// Browser info
if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT']))
{
    preg_match('#(?P<name>Firefox|Chrome)/(?P<version>\d+.\d+)#', $_SERVER['HTTP_USER_AGENT'], $browser);
    $app['browser.name'] = strtolower($browser['name']);
}

// Browser Log register
if ($app['debug'])
{
    if ($app['browser.name'] == 'firefox')
        $handler = new Monolog\Handler\FirePHPHandler();
    elseif($app['browser.name'] == 'chrome')
        $handler = new Monolog\Handler\ChromePHPHandler();
    else
        $handler = null;

    $app['monolog'] = $app->share($app->extend('monolog', function ($monolog, $app) use ($handler) {
        if ($handler)
            $monolog->pushHandler($handler);
        return $monolog;
    }));
}

// Helper Services
$app['auth.login'] = $app->share(function ($app) {
    return new Auth\Authentication($app['session']);
});
$app['auth.permission'] = $app->share(function ($app) {
    return new Auth\Authorization($app['session']);
});
$app['dao'] = $app->share(function ($app) {
    return new Model\DAO\Engine\DaoLoader($app['db']);
});
$app['bo'] = $app->share(function ($app) {
    return new BO\Engine\BoLoader($app['dao']);
});

// Starting Session
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.options' => array(
        'name' => '_' . APP_NAME,
        'id'   => uniqid('_' . APP_NAME)
    )
));
$app['session']->start();

// SQL Logger register
if ($app['debug'])
    $app['db.config']->setSQLLogger(new Log\SilexSkeletonSQLLogger($app['session'], $app['monolog']));

// ==================================================
//     Permission Filter
// ==================================================
$app->before(function (Request $request) use ($app) {
    $route = $request->attributes->get('_route');

    if (!$app['auth.permission']->freePass($route))
    {
        if (!$app['auth.login']->isAuthenticated())
            return $app->redirect('/login');

        if (!$app['auth.permission']->isAuthorized($route))
            return $app->abort(403, $route . ' - Você não pode acessar esta área!');
    }
});

// Register routes
Helper\Routes::register($app);

//=====================================================
//              HTTP Errors
//=====================================================
$app->error(function(\Exception $e, $code) use ($app) {
    if (!$app['debug'])
        return $app['twig']->render("errors/$code.twig", array(
            'error' => $e->getMessage()
        ));
});

return $app;