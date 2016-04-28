<?php

require_once __DIR__."/../config/config.php";
require_once __DIR__."/../src/If2Engine.php";

const BASE_PATH = '/imomushi';

$routes = [];
foreach ($config['pipeline_definition'] as $k => $v)
{
	foreach ($v['segments'] as $segment)
	{
		if ($segment['type'] === 'input' && $segment['protocol'] === 'http')
		{
			$routes[BASE_PATH.$segment['path']] = $k;
		}
	}
}

$uri = explode('?', $_SERVER['REQUEST_URI']);
$path = $uri[0];

if (array_key_exists($path, $routes))
{
	$if = new If2Engine();
	$if->sendRequestInput($routes[$path]);
	$result = $if->receiveRequestOutput();
	echo json_encode($result);
}
else
{
	header("HTTP/1.1 404 Not Found");
	echo "API not found";
}

