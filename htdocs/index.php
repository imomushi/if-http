<?php

require_once __DIR__."/../config/config.php";
require_once __DIR__."/../src/If2Engine.php";

// Routing Settings
$routes = [];
foreach ($config['pipeline_definition'] as $k => $v)
{
	foreach ($v['segments'] as $id => $segment)
	{
		if ($segment['type'] === 'input' && $segment['protocol'] === 'http')
		{
			$dependencies = [];
			foreach ($v['dependencies'] as $d)
			{
				if ($v['segments'][$d['to']]['type'] === 'output' && $v['segments'][$d['to']]['protocol'] === 'http')
				{
					$dependencies[] = $d['from'];
				}
			}
            $method = (isset($segment['method'])) ? $segment['method'] : 'get';
			$routes[$method.':'.$segment['path']] = ['pipeline' => $k, 'direct' => ($dependencies === [$id])];
		}
	}
}

$uri = explode('?', $_SERVER['REQUEST_URI']);
$path = $uri[0];
$method = strtolower($_SERVER["REQUEST_METHOD"]);
$key = $method.':'.$path;

if (array_key_exists($key, $routes))
{
	$if2engine = new If2Engine();
	$if2engine->sendRequestInput($routes[$key]['pipeline']);
	if ($routes[$key]['direct'])
	{
		echo "Success";
	}
	else
	{
		$result = $if2engine->receiveRequestOutput();
        echo $result['output'];
	}
}
else
{
	header("HTTP/1.1 404 Not Found");
	echo "API not found";
}

