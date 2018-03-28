<?php

	if (!defined('CORE_PATH')) {
		die('The requested page was not configured. (Missing CORE_PATH definition).');
	}

	session_start();

	require_once(CORE_PATH . 'inc/IoHelper.php');

	global $Io, $SiteSettings, $Db;

	// Setup IoHelper
	$Io = new N2f\IoHelper(CORE_PATH);
	$SiteSettings = json_decode($Io->getContents("~/siteSettings.config"), true);

	// Setup PDO Db object
	if (array_key_exists('DbName', $SiteSettings) && !empty($SiteSettings['DbName'])) {
		$Db = new PDO("mysql:host={$SiteSettings['DbHost']};dbname={$SiteSettings['DbName']}", $SiteSettings['DbUser'], $SiteSettings['DbPass']);
		$Db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	// Core files
	$Io->load('~/inc/Enum.php');
	$Io->load('~/inc/Logger.php');
	$Io->load('~/inc/ParameterHelper.php');
	$Io->load('~/inc/Paginate.php');
	$Io->load('~/inc/Constants.php');
	$Io->load('~/inc/Functions.php');
	$Io->load('~/inc/ReturnHelper.php');
	$Io->load('~/inc/ConsoleHelper.php');
	$Io->load('~/inc/ScriptUsageHelper.php');
	$Io->load('~/inc/BackendMenuHelper.php');
	$Io->load('~/inc/ApiResponse.php');
	$Io->load('~/inc/ApiRequest.php');
	$Io->load('~/inc/ApiBase.php');
	$Io->load('~/inc/ApiHandler.php');
	$Io->load('~/inc/Page.php');
	$Io->load('~/libs/phpmailer/class.phpmailer.php');
	$Io->load('~/libs/phpmailer/class.smtp.php');
	$Io->load('~/libs/markdown/Markdown.inc.php');
	$Io->load('~/libs/kses/kses.php');
	$Io->load('~/inc/classes/Classes.php');
	$Io->load('~/inc/repos/Repos.php');

	// Register library loading
	spl_autoload_register('doAutoload');
