<?php

use Framework\Http\Request;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

$request = new Request($_GET, $_POST);

$name = $request->getQueryParams()['name'] ?? 'Guest';

echo 'Hello ' . $name;