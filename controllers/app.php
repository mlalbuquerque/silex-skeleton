<?php

use Symfony\Component\HttpFoundation\Request as Request,
    Symfony\Component\HttpFoundation\Response as Response;

// CONSTS
define('__ROOT__', __DIR__.'/..');
define('__WEBROOT__', __ROOT__.'/web');
define('__UPLOAD_PATH__', __WEBROOT__.'/midia');

// Bootstraping e Registrando novas bibliotecas
$loader = require_once __DIR__.'/../vendor/autoload.php';

// Iniciando a App e ligando o debug
$app = new Silex\Application();
date_default_timezone_set('America/Sao_Paulo'); // Timezone padrão
$app['debug'] = false;

// Registrando o Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'DB',
        'user'      => 'admin',
        'password'  => '12345'
    )
));

// Registrando o Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __ROOT__.'/views'
));

// Criando novos serviços
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

// Iniciando a sessão
$app->register(new Silex\Provider\SessionServiceProvider());
//var_dump(get_class_methods(get_class($app['session']))); die;
$app['session']->start();

// ==================================================
//     Filtros (antes e depois das requisições)
// ==================================================
$app->before(function (Request $request) use ($app) {
    $route = $request->attributes->get('_route');

    if (!$app['auth.permission']->freePass($route))
    {
        if (!$app['auth.login']->isAuthenticated())
            return $app->redirect('/login');

        if (!$app['auth.permission']->isAuthorized($route))
            return $app->abort(403, 'Você não pode acessar esta área!');
    }
});

// ==================================================
//             URL's da Aplicação
// ==================================================

// ------------ AUTH ---------------------------------
$app->get('/login', function() use ($app) {
    return $app['twig']->render('auth/login.twig', array(
        'error' => ''
    ));
})->bind('auth.login');

$app->post('/authenticate', function (Request $request) use ($app) {
    $user = $app['auth.login']->getUser();

    if (!empty($user)) // Pode modificar para testar outras coisas
    {
        $app['session']->set('user', $user);
        return $app->redirect('/');
    }
    else
    {
        return $app['twig']->render('auth/login.twig', array(
            'error' => 'Login falhou! Tente novamente.'
        ));
    }
})->bind('auth.authenticate');

$app->get('/logout', function () use ($app) {
    $app['session']->remove('user');
    return $app->redirect('/login');
})->bind('auth.logout');

// ------ HOMEPAGE -----------------------
$app->get('/', function (Request $request) use ($app) {
    return $app['twig']->render('home.twig');
})->bind('homepage');

//=====================================================
//    Possíveis erros HTTP
//=====================================================
$app->error(function(\Exception $e, $code) use ($app) {
    if (!$app['debug'])
        return $app['twig']->render("errors/$code.twig", array(
            'error' => $e->getMessage()
        ));
});

return $app;
