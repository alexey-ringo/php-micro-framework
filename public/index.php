<?php

use Framework\Http\Request;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

//$request = new Request();
//$request->withQueryParams($_GET);
//$request->withParsedBody($_POST);
$request = (new Request())
            ->withQueryParams($_GET)
            ->withParsedBody($_POST);

$name = $request->getQueryParams()['name'] ?? 'Guest';

echo 'Hello ' . $name;