<?php

use Framework\Http\Request;

chdir(dirname(__DIR__));
require 'src/Framework/Http/Request.php';

$request = new Request();

$name = $request->getQueryParams()['name'] ?? 'Guest';

echo 'Hello ' . $name;