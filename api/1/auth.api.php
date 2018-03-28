<?php

	class AuthApiStrings {
		const Field_Email = 'email';
		const Field_Password = 'password';
		const Field_Token = 'token';
	}

	/**
	 * Class to group the authentication
	 * API requests.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 */
	class AuthApi extends N2f\ApiBase {
		/**
		 * Handles API-based authentication and returns an
		 * ApiSession token for future authorization.
		 * 
		 * @param N2f\ApiRequest $request Object including request data and other information.
		 * @param array $matches Optional array of regex matches from URL based off endpoint pattern.
		 */
		public function doAuth(N2f\ApiRequest $request, array $matches = null) {
			$ret = new N2f\ApiResponse();
			$params = $request->getParameterizedInput();
			$userRepo = new Users($this->db, $this->log);
			$loginRepo = new LoginKeys($this->db, $this->log);

			if ($params->hasValue(AuthApiStrings::Field_Email) && $params->hasValue(AuthApiStrings::Field_Password)) {
				$user = $userRepo->getByUsernameOrEmail($params->getString(AuthApiStrings::Field_Email), 1);

				if ($user !== null && $user->id > 0 && $user->emailConfirmed === true) {
					$key = $loginRepo->getForUserAndProvider($user->id, LoginKey::PROVIDER_BASIC);

					if ($key !== null) {
						if (password_verify($params->getString(AuthApiStrings::Field_Password), $key->key)) {
							if (password_needs_rehash($key->key, PASSWORD_DEFAULT)) {
								$key->key = password_hash($params->getString(AuthApiStrings::Field_Password), PASSWORD_DEFAULT);

								try {
									$key->update();
								} catch (Exception $ex) {
									$this->log->warning("Failed to rehash password for user: {value}\n{ex}", array('value' => $user->username, 'ex' => $ex->getMessage()));
								}
							}

							try {
								$user->lastLogin = new DateTime('now', new DateTimeZone('UTC'));
								$user->update();
							} catch (Exception $ex) {
								$this->log->warning("Failed to update user's last login: {value}\n{ex}", array('value' => $user->username, 'ex' => $ex->getMessage()));
							}

							$sess = new ApiSession($this->db, $this->log);
							$sess->userId = $user->id;
							$sess->token = env_get_guid(false);
							$sess->hostname = gethostbyaddr($_SERVER[PhpStrings::Server_Remote_Addr]);
							$sess->address = $_SERVER[PhpStrings::Server_Remote_Addr];

							try {
								$sess->create();

								if ($sess->id > 0) {
									$ret->setHttpCode(N2f\HttpStatusCodes::OK);
									$ret->setData(array('token' => $sess->token, 'userId' => $sess->userId));
								} else {
									$this->log->error("Failed to create API session, unknown error for user: {value}", array('value' => $user->username));
									$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
									$ret->setData("Unknown error creating session token, please contact an administrator");
								}
							} catch (Exception $ex) {
								$this->log->error("Failed to create API session for user: {value}\n{ex}", array('value' => $user->username, 'ex' => $ex->getMessage()));
								$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
								$ret->setData("Failed to create API session, please contact an administrator");
							}
						} else {
							$this->log->error("Invalid password provided for user: {value}", array('value' => $user->username));
							$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
							$ret->setData("Invalid credentials provided");
						}
					} else {
						$this->log->error("Login failed because of missing BASIC key for user: {value}", array('value' => $user->username));
						$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
						$ret->setData("No login available for user");
					}
				} else {
					$this->log->error("Login failed because of bad username/email: {value}", array('value' => $params->getString(AuthApiStrings::Field_Email)));
					$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
					$ret->setData("Invalid credentials provided");
				}
			} else {
				$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
				$ret->setData("Invalid parameter combination for authorization");
			}

			return $ret;
		}

		/**
		 * Handles destruction of ApiSession via
		 * API request.
		 * 
		 * @param N2f\ApiRequest $request Object including request data and other information.
		 * @param array $matches Optional array of regex matches from URL based off endpoint pattern.
		 */
		public function doLogout(N2f\ApiRequest $request, array $matches = null) {
			$ret = new N2f\ApiResponse();
			$params = $request->getParameterizedInput();
			$sessRepo = new ApiSessions($this->db, $this->log);

			if ($params->hasValue(AuthApiStrings::Field_Token)) {
				$sess = $sessRepo->getByToken($params->getString(AuthApiStrings::Field_Token));

				if ($sess !== null) {
					try {
						$sess->delete();

						$ret->setHttpCode(N2f\HttpStatusCodes::OK);
						$ret->setData("Successfully logged out");
					} catch (Exception $ex) {
						$this->log->error("Failed to delete session: {value}\n{ex}", array('value' => $sess->token, 'ex' => $ex->getMessage()));
						$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
						$ret->setData("Failed to delete session, please contact an administrator with value: '{$sess->token}'");
					}
				} else {
					$this->log->error("Invalid session token provided for logout: {value}", array('value' => $params->getString(AuthApiStrings::Field_Token)));
					$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
					$ret->setData("Invalid session token provided");
				}
			} else {
				$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
				$ret->setData("Invalid parameters for logout");
			}

			return $ret;
		}
	}

	global $Db, $Log;
	$authApi = new AuthApi($Db, $Log);

	N2f\ApiHandler::registerEndpoint("POST", "/^authenticate$/", array($authApi, 'doAuth'));
	N2f\ApiHandler::registerEndpoint("POST", "/^logout$/", array($authApi, 'doLogout'));
