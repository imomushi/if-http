<?php

class If2Engine
{
	const FILE_REQUEST_INPUT  = '/tmp/user_input.txt';
	const FILE_REQUEST_OUTPUT = '/tmp/user_output.txt';
	const SLEEP_MSEC = 10;

	private $id;
	private $file_line_count;

	
	public function __construct()
	{
		$this->id = 1;
		$this->file_line_count[self::FILE_REQUEST_OUTPUT] = $this->countLine(self::FILE_REQUEST_OUTPUT);
	}

	public function receiveRequestOutput()
	{
		while (1)
		{
			foreach ($this->checkQue(self::FILE_REQUEST_OUTPUT) as $output)
			{
				if ($output->request_id === $this->id)
				{
					return (array)$output->args;
				}
			}
		}
	}

	private function countLine($file_path)
	{
		$fp = fopen($file_path, 'r' );
		for( $count = 0; fgets( $fp ); $count++ );
		fclose($fp);
		return $count;
	}

	private function checkQue($file_name)
	{
		$fp = fopen( $file_name, 'r' );
		$count = 0;
		$new_lines = [];

		while($line = fgets( $fp ))
		{
			$count++;
			if ($count > $this->file_line_count[$file_name])
			{
				$this->file_line_count[$file_name] = $count;
				$new_lines[] = json_decode($line);
			}
		}
		fclose($fp);
		return $new_lines;
	}

	public function sendRequestInput($pipeline_id)
	{
		$args = [
			'headers' => getallheaders(),
			'cookies' => $_COOKIE,
			'params'  => array_merge($_POST, $_GET),
			'path'    => $_SERVER['REQUEST_URI'],
		];
		
		$input = json_encode(['request_id' => $this->id, 'pipeline_id' => $pipeline_id, 'args' => $args]);
		file_put_contents(self::FILE_REQUEST_INPUT, $input.PHP_EOL, FILE_APPEND);
	}
}
