<?php

	define('CORE_PATH', '../');
	require(CORE_PATH . 'inc/Core.php');

	global $SiteSettings, $Db;

	if (array_key_exists(BackendStrings::Session_ApiKey, $_SESSION)) {
		$apiRepo = new ApiSessions($Db);
		$session = $apiRepo->getByToken($_SESSION[BackendStrings::Session_ApiKey]);

		if ($session !== null) {
			try {
				$session->delete();
			} catch (Exception $ex) { }
		}

		unset($_SESSION[BackendStrings::Session_ApiKey]);
	}

	if (array_key_exists(BackendStrings::Session_UserIdKey, $_SESSION)) {
		unset($_SESSION[BackendStrings::Session_UserIdKey]);
	}

	header('Location: ./index.php');
	exit;
