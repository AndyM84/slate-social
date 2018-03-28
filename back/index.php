
<?php

	class LoginStrings {
		const Field_Email = 'login-email';
		const Field_Password = 'login-password';
		const Field_RememberMe = 'login-remember-me';
	}

	define('CORE_PATH', '../');
	require(CORE_PATH . 'inc/Core.php');

	global $Db;

	$log = new N2f\Logger();
	$logFile = 'backend-' . date('Y-m-d') . '.log';
	$post = new N2f\ParameterHelper($_POST);
	$session = new N2f\ParameterHelper($_SESSION);

	if ($session->hasValue(BackendStrings::Session_ApiKey)) {
		$apiRepo = new ApiSessions($Db, $log);
		$apiSession = $apiRepo->getByToken($session->getString(BackendStrings::Session_ApiKey));

		if ($apiSession->id > 0) {
			header('Location: ./dashboard.php');
			exit;
		}
	}

	if ($post->hasValue(LoginStrings::Field_Email) && $post->hasValue(LoginStrings::Field_Password)) {
		$userRepo = new Users($Db, $log);
		$roleRepo = new UserRoles($Db, $log);
		$rememberMe = ($post->hasValue(LoginStrings::Field_RememberMe)) ? true : false;
		$user = $userRepo->getByUsernameOrEmail($post->getString(LoginStrings::Field_Email));

		if ($user === null) {
			$error = "Invalid account provided";
			$log->error("Invalid username or email provided for user: {value}", array('value' => $post->getString(LoginStrings::Field_Email)));
		} else {
			$keyRepo = new LoginKeys($Db, $log);
			$key = $keyRepo->getForUserAndProvider($user->id, LoginKey::PROVIDER_BASIC);

			if ($key === null) {
				$error = "Invalid account provided";
				$log->error("Account missing BASIC login key: {username}", array('username' => $user->username));
			} else {
				if (password_verify($post->getString(LoginStrings::Field_Password), $key->key) && $roleRepo->userInRoleByRoleName($user->id, BackendStrings::Admin_Role)) {
					if (password_needs_rehash($key->key, PASSWORD_DEFAULT)) {
						$key->key = password_hash($post->getString(LoginStrings::Field_Password), PASSWORD_DEFAULT);
						
						try {
							$key->update();
						} catch (Exception $ex) {
							$log->warning("Failed to rehash password for user: {value}", array('value' => $user->username));
						}
					}

					try {
						$user->lastLogin = new DateTime('now', new DateTimeZone('UTC'));
						$user->update();
					} catch (Exception $ex) {
						$log->error("Failed to update user's last login: {username}", array('username' => $user->username));
					}

					$sess = new ApiSession($Db, null, $log);
					$sess->userId = $user->id;
					$sess->token = env_get_guid(false);
					$sess->hostname = gethostbyaddr($_SERVER[PhpStrings::Server_Remote_Addr]);
					$sess->address = $_SERVER[PhpStrings::Server_Remote_Addr];

					try {
						$sess->create();

						if ($sess->id > 0) {
							$_SESSION[BackendStrings::Session_ApiKey] = $sess->token;
							$_SESSION[BackendStrings::Session_UserIdKey] = $sess->userId;

							header('Location: ./dashboard.php');
							exit;
						} else {
							$error = "Failed to create API session, no id";
						}
					} catch (Exception $ex) {
						$error = "Failed to create API session";
					}
				} else {
					$error = "Invalid credentials provided";
					$log->error("Invalid password provided for user: {value}", array('value' => $post->getString(LoginStrings::Field_Email)));
				}
			}
		}
	}

	$log->outputToFile($logFile);

?>
<!DOCTYPE html>
<html class="no-js">
	<head>
		<!-- Meta Info -->
		<meta charset="utf-8" />
		<title><?php echo $SiteSettings[SettingsStrings::SiteTitle]; ?> - Login</title>
		<meta name="description" content="<?php echo $SiteSettings[SettingsStrings::SiteTitle]; ?> - Login" />
		<meta name="robots" content="noindex, nofollow" />
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0" />
		<!-- END Meta Info -->

		<!-- Icons -->
		<link rel="shortcut icon" href="img/favicon.ico" />
		<link rel="apple-touch-icon" href="img/icon57.png" sizes="57x57" />
		<link rel="apple-touch-icon" href="img/icon72.png" sizes="72x72" />
		<link rel="apple-touch-icon" href="img/icon76.png" sizes="76x76" />
		<link rel="apple-touch-icon" href="img/icon114.png" sizes="114x114" />
		<link rel="apple-touch-icon" href="img/icon120.png" sizes="120x120" />
		<link rel="apple-touch-icon" href="img/icon144.png" sizes="144x144" />
		<link rel="apple-touch-icon" href="img/icon152.png" sizes="152x152" />
		<!-- END Icons -->

		<!-- Stylesheets -->
		<link rel="stylesheet" href="./css/bootstrap.css" />
		<link rel="stylesheet" href="./css/plugins.css" />
		<link rel="stylesheet" href="./css/main.css" />
		<link rel="stylesheet" href="./css/themes.css" />
		<!-- END Stylesheets -->

		<!-- Modernizr (browser feature detection library) & Respond.js (Enable responsive CSS code on browsers that don't support it, eg IE8) -->
		<script src="js/vendor/modernizr-2.7.1-respond-1.4.2.min.js"></script>
	</head>
	<body class="login no-animation">
		<!-- Login Logo -->
		<a href="#login-form-tab" class="login-btn themed-background-default">
			<span class="login-logo">
				<span class="square1 themed-border-default"></span>
				<span class="square2"></span>
			</span>
		</a>
		<!-- END Login Logo -->

		<!-- Login Container -->
		<div id="login-container">
			<!-- Login Block -->
			<div class="block-tabs block-themed">
				<ul id="login-tabs" class="nav nav-tabs" data-toggle="tabs">
					<li class="active text-center">
						<a href="#login-form-tab">
							<i class="fa fa-user"></i> Login
						</a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="login-form-tab">
<?php

	if (isset($error)) { ?>
						<div class="alert alert-danger">
							<i class="fa fa-times-circle"></i> <?php echo($error); ?>
						</div>
<?php	}

?>
						<!-- Login Form -->
						<form action="index.php" method="post" id="login-form" class="form-horizontal">
							<div class="form-group">
								<div class="col-xs-12">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-envelope-o fa-fw"></i></span>
										<input type="text" id="login-email" name="login-email" class="form-control" placeholder="Email or Username.." />
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-xs-12">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-asterisk fa-fw"></i></span>
										<input type="password" id="login-password" name="login-password" class="form-control" placeholder="Password.." />
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-xs-12 clearfix">
									<div class="pull-right">
										<button type="submit" class="btn btn-success remove-margin">Login</button>
									</div>
								</div>
							</div>
						</form>
						<!-- END Login Form -->
					</div>
				</div>
			</div>
			<!-- END Login Block -->
		</div>
		<!-- END Login Container -->

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script>!window.jQuery && document.write(unescape('%3Cscript src="js/vendor/jquery-1.11.0.min.js"%3E%3C/script%3E'));</script>
		<script src="js/vendor/bootstrap.min.js"></script>
		<script src="js/plugins.js"></script>
		<script src="js/main.js"></script>
	</body>
</html>