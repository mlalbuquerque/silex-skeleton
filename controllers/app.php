<?php

use Symfony\Component\HttpFoundation\Request as Request,
    Symfony\Component\HttpFoundation\Response as Response;

// CONSTS
define('__ROOT__', __DIR__.'/..');
define('__WEBROOT__', __ROOT__.'/web');
define('__UPLOAD_PATH__', __WEBROOT__.'/midia');
// Nome do atributo do usuário para verificação de autorização
define('__USER_AUTH_ATTR__', 'nome_perfil');
// Nome do método a ser chamado pelo Logger para registrar quem está atuando
define('__USERNAME_METHOD_LOGGED__', '__toString');

// Bootstraping e Registrando novas bibliotecas
$loader = require_once __DIR__.'/../vendor/autoload.php';

// Iniciando a App e ligando o debug
$app = new Silex\Application();
date_default_timezone_set('America/Sao_Paulo'); // Timezone padrão
$app['debug'] = true;

// Registrando o Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => require_once __ROOT__ . '/config/database.php'
));

// Registrando o Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __ROOT__.'/views'
));

// Registrando o Monolog - objeto para logging
$app->register(new Log\LoggerServiceProvider(), array(
    'monolog.name'    => 'MYAPP', // Pode trocar para o nome da sua aplicação
    'monolog.level'   => $app['debug'] ? \Monolog\Logger::DEBUG : \Monolog\Logger::WARNING,
    'monolog.logfile' => __ROOT__ . '/log/silex.log',
    'monolog.handler' => function () use ($app) {
        // Pode-se mudar o handler do log. Para mais classes, veja https://github.com/Seldaek/monolog#handlers
        return new Monolog\Handler\RotatingFileHandler($app['monolog.logfile'], $app['monolog.level']);
    }
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
$app['session']->start();

// Registrando o Logger de SQL apenas para debug
if ($app['debug'])
    $app['db.config']->setSQLLogger(new Log\SilexSkeletonLogger($app['session'], $app['monolog']));

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
            return $app->abort(403, $route . ' - Você não pode acessar esta área!');
    }
});

// ==================================================
//             URL's da Aplicação
// ==================================================

// ------------ AUTH Example ------------------------
$app->get('/login', function() use ($app) {
    return $app['twig']->render('auth/login.twig', array(
        'error' => ''
    ));
})->bind('auth.login');

$app->post('/authenticate', function (Request $request) use ($app) {
    // Modifique o método getUser() em lib/Auth/Authentication.php
    $user = $app['auth.login']->getUser();
    $user->setPermission($request->get('perfil'));

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

// ------ HOMEPAGE Examples --------------------
$app->get('/', function (Request $request) use ($app) {
    return $app['twig']->render('home.twig');
})->bind('homepage');

$app->get('/admin', function () use ($app) {
    return $app['twig']->render('admin/main.twig');
})->bind('admin.main');

//=====================================================
//    CONTROLADORES
//=====================================================
// Basta incluir um arquivo que está na pasta "controllers"
require_once 'usuario.php';

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
