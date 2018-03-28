<?php

	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	define('CORE_PATH', './');
	require(CORE_PATH . 'inc/Core.php');

	global $Db, $SiteSettings;

	$log = new N2f\Logger();
	$ch = new N2f\ConsoleHelper($argc, $argv);
	$logFile = 'passwd-' . date('Y-m-d') . '.log';
	$script = new N2f\ScriptUsageHelper("ZTC Password Manager v1.0", "Script to manage user passwords from the command line.");

	$script->addExample("--user AUser --set");
	$script->addExample("--user AUser --remove");

	$script->addOption("user", "u", "user", "php .\scripts\passwd.php -u AUser", "Sets which user is being modified", "Sets which user is being modified, by username");
	$script->addOption("set", "s", "set", "php .\scripts\passwd.php -s", "Sets the password value", "Sets password for a user, must be used with the `user` option");
	$script->addOption("remove", "r", "remove", "php .\scripts\passwd.php -r", "Removes the user's password", "Removes password for a user, must be used with the `user` option");

	function getPassword(N2f\ConsoleHelper $ch, Users $repo, N2f\Logger $log = null, $isConfirmation = false) {
		$password = $ch->getQueriedInput(
			($isConfirmation === true) ? "Confirm Password" : "Password",
			null,
			"Invalid password provided",
			5,
			function ($value) use ($repo) {
				return $repo->isValidPassword($value);
			}
		);

		if ($password->isBad()) {
			$ch->putLine("Password input failed with error(s): ");
			$ch->putLine("\t" . implode("\n\t", $password->getMessages()));

			if ($log !== null) {
				$log->critical("Password input failed with error(s): \n\t" . implode("\n\t", $password->getMessages()));
			}

			return false;
		}

		return $password->getResults();
	}

	function getConfirmation(N2f\ConsoleHelper $ch, N2f\Logger $log = null) {
		$confirm = $ch->getQueriedInput(
			"Really proceed? This cannot be undone.. (Y/N)",
			null,
			"Invalid confirmation code, must be Y or N",
			2,
			function ($value) {
				return strtolower(trim($value)) == 'y' || strtolower(trim($value)) == 'n';
			},
			function ($value) {
				return strtolower(trim($value));
			}
		);

		if ($confirm->isBad()) {
			$ch->putLine("Invalid confirmation code, must by Y or N");

			return false;
		}
		
		return ($confirm->getResults() === 'y') ? true : false;
	}

	function getUserByIdentifier(PDO $db, $identifier, N2f\Logger $log = null) {
		if (is_numeric($identifier)) {
			$user = new User($db, intval($identifier), $log);

			if ($user->id < 1) {
				return null;
			}

			return $user;
		}

		$users = new Users($db, $log);
		
		return $users->getByUsername($identifier);
	}

	function setPassword(N2f\ConsoleHelper $ch, PDO $db, N2f\Logger $log = null) {
		$set = $ch->getParameterWithDefault('u', 'user', true);

		if ($set === true) {
			$ch->putLine("Missing user identifier, use `php " . $ch->getSelf() . " -h set` for more information");

			return;
		}

		$user = getUserByIdentifier($db, $set, $log);
		
		if ($user === null || $user->id < 1) {
			$ch->putLine("No user found with identifier '{$set}'");

			return;
		}

		$ch->putLine("Set user's BASIC password by answering the following prompts...");
		$ch->putLine();

		$loginRepo = new LoginKeys($db, $log);
		$userLogin = $loginRepo->getForUserAndProvider($user->id, LoginKey::PROVIDER_BASIC);

		$password = getPassword($ch, new Users($db, $log), $log, false);

		if ($password === false || $password === null) {
			return;
		}

		$confirm = getPassword($ch, new Users($db, $log), $log, true);
		$ch->putLine();

		if ($password === false || $password === null || $confirm === false || $confirm === null) {
			$ch->putLine("Must enter passwords to proceed");

			return;
		}

		if ($password !== $confirm) {
			$ch->putLine("Must enter identical passwords to proceed");

			return;
		}

		if ($userLogin === null) {
			$userLogin = new LoginKey($db, null, null, $log);
			$userLogin->provider = LoginKey::PROVIDER_BASIC;
			$userLogin->userId = $user->id;
			$userLogin->key = password_hash($password, PASSWORD_DEFAULT);

			try {
				$userLogin->create();
				$ch->putLine("Successfully created user's BASIC login key");
			} catch (Exception $ex) {
				$ch->putLine("Failed to create user's password, see log for more details");
			}
		} else {
			$userLogin->key = password_hash($password, PASSWORD_DEFAULT);

			try {
				$userLogin->update();
				$ch->putLine("Successfully updated user's BASIC login key");
			} catch (Exception $ex) {
				$ch->putLine("Failed to update user's password, see log for more details");
			}
		}

		return;
	}

	function removePassword(N2f\ConsoleHelper $ch, PDO $db, N2f\Logger $log = null) {
		$rem = $ch->getParameterWithDefault('u', 'user', true);

		if ($rem === true) {
			$ch->putLine("Missing user identifier, use `php " . $ch->getSelf() . " -h remove` for more information");

			return;
		}

		$user = getUserByIdentifier($db, $rem, $log);
		
		if ($user === null || $user->id < 1) {
			$ch->putLine("No user found with identifier '{$rem}'");

			return;
		}

		$ch->putLine("Remove user's BASIC password by answering the following prompts...");
		$ch->putLine();

		$loginRepo = new LoginKeys($db, $log);
		$userLogin = $loginRepo->getForUserAndProvider($user->id, LoginKey::PROVIDER_BASIC);

		if ($userLogin === null) {
			$ch->putLine("User does not have a BASIC login key, nothing to remove");

			return;
		}

		$confirm = getConfirmation($ch, $log);
		$ch->putLine();

		if ($confirm === true) {
			try {
				$userLogin->delete();
				$ch->putLine("Successfully deleted user's BASIC login key");
			} catch (Exception $ex) {
				$ch->putLine("Failed to delete user login key, see log for more details");
			}
		} else {
			$ch->putLine("Aborted login key deletion per user request");
		}

		return;
	}

	if ($ch->numArgs() < 2) {
		$script->showBasicHelp($ch, "No options provided");

		exit;
	}

	if ($ch->hasShortLongArg('h', 'help', true)) {
		$script->showOptionHelp($ch);

		exit;
	}

	if (!$ch->hasShortLongArg('u', 'user', true)
		|| (!$ch->hasShortLongArg('s', 'set', true) && !$ch->hasShortLongArg('r', 'remove', true))) {
		$script->showBasicHelp($ch, "Must provide `user` option as well as either `set` or `remove` option");

		exit;
	}

	$ch->putLine($script->name);
	$ch->putLine();

	if ($ch->hasShortLongArg('s', 'set', true)) {
		setPassword($ch, $Db, $log);
	} else if ($ch->hasShortLongArg('r', 'remove', true)) {
		removePassword($ch, $Db, $log);
	}

	$log->outputToFile($logFile);
