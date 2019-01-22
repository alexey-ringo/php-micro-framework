<?php

use Framework\Http\Middleware\RouteMiddleware;
use Framework\Http\Middleware\DispatchMiddleware;

use App\Http\Middleware\ErrorHandlerMiddleware;
use App\Http\Middleware\CredentialsMiddleware;
use App\Http\Middleware\ProfilerMiddleware;
use App\Http\Middleware\BasicAuthMiddleware;

/** @var \Framework\Container\Container $container */
/** @var \Framework\Http\Application $app */

$app->pipe($container->get(ErrorHandlerMiddleware::class));
$app->pipe(CredentialsMiddleware::class);
$app->pipe(ProfilerMiddleware::class);
$app->pipe($container->get(RouteMiddleware::class));
$app->pipe('cabinet', $container->get(BasicAuthMiddleware::class));
$app->pipe($container->get(DispatchMiddleware::class));
