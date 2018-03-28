<?php

	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	define('CORE_PATH', './');
	require(CORE_PATH . 'inc/Core.php');

	global $Db, $SiteSettings;

	$log = new N2f\Logger();
	$ch = new N2f\ConsoleHelper($argc, $argv);
	$logFile = 'usermgr-' . date('Y-m-d') . '.log';
	$script = new N2f\ScriptUsageHelper("ZTC User Manager v1.0", "Script to manage user accounts from the command line.");

	$script->addExample("--read=1");
	$script->addExample("--edit=1");
	$script->addExample("--delete=1");
	$script->addExample("--make-admin=1");
	$script->addExample("--remove-admin=1");

	$script->addOption("create", "c", "create", "php .\scripts\usermgr.php -c", "Triggers user creation", "Starts an interactive session to create a user");
	$script->addOption('read', 'r', 'read={USER_ID}', 'php .\scripts\usermgr.php -r 1', "Displays user information", "Retrieves and displays a user's information");
	$script->addOption("edit", "e", "edit={USER_ID}", "php .\scripts\usermgr.php -e 1", "Edits a user", "Starts an interactive session to edit a user");
	$script->addOption('delete', 'd', 'delete={USER_ID}', 'php .\scripts\usermgr.php -d 1', "Deletes a user", "Starts an interactive session to delete a user");
	$script->addOption('make-admin', 'ma', 'make-admin={USER_ID}', 'php .\scripts\usermgr.php -ma 1', "Makes a user an admin", "Adds the administrator role to a user account");
	$script->addOption('remove-admin', 'ra', 'remove-admin={USER_ID}', 'php .\scripts\usermgr.php -ra 1', "Removes user as an admin", "Removes the administrator role to a user account");

	function getUsername($existing, N2f\ConsoleHelper $ch, Users $repo, N2f\Logger $log = null) {
		$username = $ch->getQueriedInput(
			"Username",
			$existing,
			"Invalid username provided",
			5,
			function ($value) use ($repo) {
				return $repo->isValidUsername($value, true);
			}
		);

		if ($username->isBad()) {
			$ch->putLine("User input failed with error(s): ");
			$ch->putLine("\t" . implode("\n\t", $username->getMessages()));

			if ($log !== null) {
				$log->critical("User input failed with error(s): \n\t" . implode("\n\t", $username->getMessages()));
			}

			return false;
		}

		return $username->getResults();
	}

	function getEmail($existing, N2f\ConsoleHelper $ch, Users $repo, N2f\Logger $log = null) {
		$email = $ch->getQueriedInput(
			"Email Address",
			$existing,
			"Invalid email address provided",
			5,
			function ($value) use ($repo) {
				return $repo->isValidEmail($value, true);
			}
		);

		if ($email->isBad()) {
			$ch->putLine("Email input failed with error(s): ");
			$ch->putLine("\t" . implode("\n\t", $email->getMessages()));

			if ($log !== null) {
				$log->critical("Email input failed with error(s): \n\t" . implode("\n\t", $email->getMessages()));
			}

			return false;
		}

		return $email->getResults();
	}

	function getEmailConfirmed($existing, N2f\ConsoleHelper $ch, N2f\Logger $log = null) {
		$confirmed = $ch->getQueriedInput(
			"Email Confirmed (Y/N)",
			$existing,
			"Email confirmed must be 'Y' or 'N'",
			5,
			function ($value) {
				$ret = new N2f\ReturnHelper();

				if (strtolower($value) === 'y' || strtolower($value) === 'n') {
					$ret->setGood();
				} else {
					$ret->setBad();
				}

				return $ret;
			},
			function ($value) {
				return strtolower(trim($value));
			}
		);

		if ($confirmed->isBad()) {
			$ch->putLine("Email confirmed input failed with error(s): ");
			$ch->putLine("\t" . implode("\n\t", $confirmed->getMessages()));

			if ($log !== null) {
				$log->critical("Email confirmed input failed with error(s): \n\t" . implode("\n\t", $confirmed->getMessages()));
			}

			return false;
		}

		return $confirmed->getResults();
	}

	function createUser(N2f\ConsoleHelper $ch, PDO $db, N2f\Logger $log = null) {
		$ch->putLine("Creating new user, answer the following queries...");
		$ch->putLine();

		$tmp = new User($db, null, $log);
		$userRepo = new Users($db, $log);

		$tmp->username = getUsername(null, $ch, $userRepo, $log);

		if ($tmp->username === false) {
			return;
		}

		$tmp->email = getEmail(null, $ch, $userRepo, $log);

		if ($tmp->email === false) {
			return;
		}

		$tmp->emailConfirmed = getEmailConfirmed(null, $ch, $log);

		if ($tmp->emailConfirmed === false) {
			return;
		}

		$tmp->emailConfirmed = ($tmp->emailConfirmed === 'y') ? true : false;

		try {
			$tmp->create();

			$ch->putLine();
			$ch->putLine("User created successfully, userId #{$tmp->id}");
			$ch->putLine();
		} catch (Exception $ex) {
			$ch->putLine("Failed to create user with error(s): \n\t" . $ex->getMessage());

			return;
		}

		return;
	}

	function readUser(N2f\ConsoleHelper $ch, PDO $db, N2f\Logger $log = null) {
		$read = $ch->getParameterWithDefault('r', 'read', true);

		if ($read === true) {
			$ch->putLine("Missing user identifier, use 'php " . $ch->getSelf() . " -h read' for more information");

			return;
		}

		$user = new User($db, intval($read), $log);

		if ($user->id < 1) {
			$ch->putLine("No user found with userId #{$read}");

			return;
		}

		$loginRepo = new LoginKeys($db, $log);
		$logins = $loginRepo->getAllForUser($user->id);

		$ch->putLine("Username: {$user->username}");
		$ch->putLine("Email: {$user->email}");
		$ch->putLine("Email Confirmed: " . (($user->emailConfirmed) ? "Yes" : "No"));
		$ch->putLine("Date Joined: " . $user->dateJoined->format("Y-m-d G:i:s"));
		$ch->putLine("Last Login: " . (($user->lastLogin === null) ? "N/A" : $user->lastLogin->format("Y-m-d G:i:s")));

		if (count($logins) > 0) {
			$ch->putLine("Available Login Keys:");

			foreach (array_values($logins) as $login) {
				$provider = '';

				switch ($login->provider) {
					case LoginKey::PROVIDER_BASIC:
						$provider = 'BASIC';

						break;
					case LoginKey::PROVIDER_FACEBOOK:
						$provider = 'FACEBOOK';

						break;
					case LoginKey::PROVIDER_TWITTER:
						$provider = 'TWITTER';

						break;
					default:
						$provider = 'UNKNOWN';

						break;
				}

				$ch->putLine("\t{$provider}");
			}
		}

		return;
	}

	function editUser(N2f\ConsoleHelper $ch, PDO $db, N2f\Logger $log = null) {
		$edit = $ch->getParameterWithDefault('e', 'edit', true);

		if ($edit === true) {
			$ch->putLine("Missing user identifier, use 'php " . $ch->getSelf() . " -h read' for more information");

			return;
		}

		$userRepo = new Users($db, $log);
		$user = new User($db, intval($edit), $log);

		if ($user->id < 1) {
			$ch->putLine("No user found with userId #{$edit}");

			return;
		}

		$user->username = getUsername($user->username, $ch, $userRepo, $log);

		if ($user->username === false) {
			return;
		}

		$user->email = getEmail($user->email, $ch, $userRepo, $log);

		if ($user->email === false) {
			return;
		}

		$user->emailConfirmed = getEmailConfirmed(($user->emailConfirmed) ? 'Y' : 'N', $ch, $log);

		if ($user->emailConfirmed === false) {
			return;
		}

		$user->emailConfirmed = ($user->emailConfirmed === 'y') ? true : false;

		try {
			$user->update();
		} catch (Exception $ex) {
			$ch->putLine("Failed to update userId #{$user->id} with error(s): \n\t" . $ex->getMessage());

			return;
		}

		return;
	}

	function deleteUser(N2f\ConsoleHelper $ch, PDO $db, N2f\Logger $log = null) {
		$delete = $ch->getParameterWithDefault('d', 'delete', true);

		if ($delete === true) {
			$ch->putLine("Missing user identifier, use 'php " . $ch->getSelf() . " -h delete' for more information");

			return;
		}

		$user = new User($db, intval($delete), $log);

		if ($user->id < 1) {
			$ch->putLine("No user found with userId #{$delete}");

			return;
		}

		$process = $ch->getQueriedInput(
			"Delete {$user->username} (Y/N)?",
			null,
			"Invalid response, must be Y or N",
			2,
			function ($value) {
				return strtolower(trim($value)) == 'y' || strtolower(trim($value)) == 'n';
			},
			function ($value) {
				return strtolower(trim($value));
			}
		);

		if ($process === false) {
			return;
		}

		if ($process == 'y') {
			try {
				$user->delete();

				$ch->putLine();
				$ch->putLine("Successfully deleted {$user->username}, userId #{$user->id}");
				$ch->putLine();
			} catch (Exception $ex) {
				$ch->putLine("Failed to delete userId #{$user->id} with error(s): \n\t" . $ex->getMessage());
			}
		}

		return;
	}

	function makeUserAdmin(N2f\ConsoleHelper $ch, PDO $db, N2f\Logger $log = null) {
		$admin = $ch->getParameterWithDefault('ma', 'make-admin', true);

		if ($admin === true) {
			$ch->putLine("Missing user identifier, use 'php " . $ch->getSelf() . " -h make-admin' for more information");

			return;
		}

		$user = new User($db, intval($admin), $log);

		if ($user->id < 1) {
			$ch->putLine("No user found with userId #{$admin}");

			return;
		}

		$roleRepo = new Roles($db, $log);
		$roleRepo->createIfMissing(BackendStrings::Admin_Role);

		$roles = new UserRoles($db, $log);

		if (!$roles->userInRoleByRoleName($user->id, BackendStrings::Admin_Role)) {
			$roles->addUserToRoleByRoleName($user->id, BackendStrings::Admin_Role);

			if (!$roles->userInRoleByRoleName($user->id, BackendStrings::Admin_Role)) {
				$ch->putLine("Failed to add user to admin role, check log for details");

				return;
			}

			$ch->putLine("Successfully made {$user->username} an administrator");

			return;
		}

		$ch->putLine("User {$user->username} was already an administrator");

		return;
	}

	function removeUserAdmin(N2f\ConsoleHelper $ch, PDO $db, N2f\Logger $log = null) {
		$admin = $ch->getParameterWithDefault('ra', 'remove-admin', true);

		if ($admin === true) {
			$ch->putLine("Missing user identifier, use 'php " . $ch->getSelf() . " -h remove-admin' for more information");

			return;
		}

		$user = new User($db, intval($admin), $log);

		if ($user->id < 1) {
			$ch->putLine("No user found with userId #{$admin}");

			return;
		}

		$roles = new UserRoles($db, $log);

		if ($roles->userInRoleByRoleName($user->id, BackendStrings::Admin_Role)) {
			$roles->removeUserFromRoleByRoleName($user->id, BackendStrings::Admin_Role);

			if ($roles->userInRoleByRoleName($user->id, BackendStrings::Admin_Role)) {
				$ch->putLine("Failed to delete user from admin role, check log for details");

				return;
			}

			$ch->putLine("Successfully removed {$user->username} as an administrator");

			return;
		}

		$ch->putLine("User {$user->username} wasn't an administrator");

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

	if (!$ch->hasShortLongArg('c', 'create', true)
		&& !$ch->hasShortLongArg('r', 'read', true)
		&& !$ch->hasShortLongArg('e', 'edit', true)
		&& !$ch->hasShortLongArg('d', 'delete', true)
		&& !$ch->hasShortLongArg('ma', 'make-admin', true)
		&& !$ch->hasShortLongArg('ra', 'remove-admin', true)) {
		$script->showBasicHelp($ch, "No trigger provided, need to specify create/read/edit/delete/make-admin/remove-admin");

		exit;
	}

	$ch->putLine($script->name);
	$ch->putLine();

	if ($ch->hasShortLongArg('c', 'create', true)) {
		createUser($ch, $Db, $log);
	} else if ($ch->hasShortLongArg('r', 'read', true)) {
		readUser($ch, $Db, $log);
	} else if ($ch->hasShortLongArg('e', 'edit', true)) {
		editUser($ch, $Db, $log);
	} else if ($ch->hasShortLongArg('d', 'delete', true)) {
		deleteUser($ch, $Db, $log);
	} else if ($ch->hasShortLongArg('ma', 'make-admin', true)) {
		makeUserAdmin($ch, $Db, $log);
	} else if ($ch->hasShortLongArg('ra', 'remove-admin', true)) {
		removeUserAdmin($ch, $Db, $log);
	}

	$log->outputToFile($logFile);
