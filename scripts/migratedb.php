<?php

	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	define('CORE_PATH', './');
	require(CORE_PATH . 'inc/Core.php');

	global $Db, $SiteSettings;

	$Ch = new N2f\ConsoleHelper($argc, $argv);
	$params = $Ch->parameters(true);

	if ($params !== null && array_key_exists('drop', $params) !== false) {
		$Ch->putLine("Preparing to drop database...");
		$Ch->putLine();

		foreach (glob('./database/drop/*.mysql') as $dropFile) {
			$Ch->put("Executing drop file '{$dropFile}': ");

			$sql = str_replace('%DbName%', $SiteSettings['DbName'], file_get_contents($dropFile));

			try {
				$Db->query($sql);
				$Ch->putLine('SUCCESS');
			} catch (PDOException $pdoex) {
				$Ch->putLine('FAIL ([' . $pdoex->getCode() . '] ' . $pdoex->getMessage() . ')');

				break;
			} catch (Exception $ex) {
				$Ch->putLine('FAIL (' . $ex->getMessage() . ')');

				break;
			}
		}

		$Ch->putLine();
		$Ch->putLine("Completed database drop.");

		exit;
	}

	try {
		$Ch->putLine("Preparing to migrate database...");
		$Ch->putLine();

		$RunScripts = array();

		if ($Db->query("SHOW TABLES LIKE 'Migrations'")->rowCount() > 0) {
			$query = $Db->query("SELECT * FROM `Migrations`");

			while ($row = $query->fetch()) {
				$RunScripts[$row['FileName']] = true;
			}
		}

		foreach (glob('./database/*.mysql') as $sqlFile) {
			$Ch->put("Executing migrate file '{$sqlFile}': ");

			if (isset($RunScripts[$sqlFile])) {
				$Ch->putLine("SKIPPING (already run)");

				continue;
			}

			$sql = str_replace('%DbName%', $SiteSettings['DbName'], file_get_contents($sqlFile));

			try {
				$Db->query($sql);
				$Db->query("INSERT INTO `Migrations` (`FileName`) VALUES ('{$sqlFile}')");

				$Ch->putLine("SUCCESS");
			} catch (PDOException $pdoex) {
				$Ch->putLine('FAIL ([' . $pdoex->getCode() . '] ' . $pdoex->getMessage() . ')');
			} catch (Exception $ex) {
				$Ch->putLine('FAIL (' . $ex->getMessage() . ')');
			}
		}
	} catch (PDOException $pdoex) {
		$Ch->putLine('PDO ERROR ([' . $pdoex->getCode() . '] ' . $pdoex->getMessage() . ')');
	} catch (Exception $ex) {
		$Ch->putLine('GENERAL ERROR (' . $ex->getMessage() . ')');
	}

	$Ch->putLine();
	$Ch->putLine("Completed database migration.");
