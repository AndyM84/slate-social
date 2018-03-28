<?php

	define('CORE_PATH', '../../');
	require(CORE_PATH . 'inc/Core.php');

	N2f\ApiHandler::registerEndpoint(null, null, function (N2f\ApiRequest $request, array $matches = null) {
		N2f\ApiHandler::setHttpResponseCode(404);
		echo(json_encode(array('message' => "Not a valid API endpoint")));

		return;
	});

	global $Io, $Db, $Log;
	$Log = new N2f\Logger();
	$logFile = 'api-' . date('Y-m-d') . '.log';
	
	$apiFiles = $Io->getFolderFiles(".");

	if (count($apiFiles) > 0) {
		foreach (array_values($apiFiles) as $file) {
			if (substr($file, -8) == '.api.php') {
				$Io->load($file);
			}
		}
	}

	$handler = N2f\ApiHandler::getInstance($Db, $Log);
	$handler->handle();

	$Log->outputToFile($logFile);
