<?php

use Framework\Container\Container;

$container = new Container(require __DIR__ . '/simpledependencies.php');

$container->set('config', require __DIR__ . '/parameters.php');

return $container;