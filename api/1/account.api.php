<?php

	class AccountApiStrings {
		const Field_Token = 'token';
	}

	/**
	 * Class to contain account API requests.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 */
	class AccountApi extends N2f\ApiBase {
		/**
		 * Retrieves account information for the logged-in user.
		 * 
		 * @param N2f\ApiRequest $request Object including request data and other information.
		 * @param array $matches Optional array of regex matches of URL based off endpoint pattern.
		 */
		public function getAccount(N2f\ApiRequest $request, array $matches = null) {
			$ret = new N2f\ApiResponse();
			$params = $request->getParameterizedInput();
			$sessRepo = new ApiSessions($this->db, $this->log);

			if ($params->hasValue(AccountApiStrings::Field_Token)) {
				$sess = $sessRepo->getByToken($params->getString(AccountApiStrings::Field_Token));
				
				if ($sess !== null) {
					if ($params->hasValue('id')) {
						$userRoles = new UserRoles($this->db, $this->log);

						if ($userRoles->userInRoleByRoleName($sess->userId, BackendStrings::Admin_Role)) {
							try {
								$user = new User($this->db, $params->getInt('id'), $this->log);

								$ret->setHttpCode(N2f\HttpStatusCodes::OK);
								$ret->setData(array(
									'userId' => $user->id,
									'email' => $user->email,
									'username' => $user->username,
									'dateJoined' => $user->dateJoined,
									'lastLogin' => $user->lastLogin
								));
							} catch (Exception $ex) {
								$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
								$ret->setData("Failed to retrieve account information");
							}
						} else {
							$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
							$ret->setData("Non-admin cannot retrieve other user account information");
						}
					} else {
						try {
							$user = new User($this->db, $sess->userId, $this->log);
						
							$ret->setHttpCode(N2f\HttpStatusCodes::OK);
							$ret->setData(array(
								'userId' => $user->id,
								'email' => $user->email,
								'username' => $user->username,
								'dateJoined' => $user->dateJoined,
								'lastLogin' => $user->lastLogin
							));
						} catch (Exception $ex) {
							$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
							$ret->setData("Failed to retrieve account information");
						}
					}
				} else {
					$this->log->error("Failed to find session for token: {value}", array('value' => $params->getString(AccountApiStrings::Field_Token)));
					$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
					$ret->setData("Invalid session data provided, please contact administrator with code: " . $params->getString(AccountApiStrings::Field_Token));
				}
			} else {
				$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
				$ret->setData("Invalid parameters provided");
			}

			return $ret;
		}

		/**
		 * Pushes update to account information for logged-in
		 * user.
		 * 
		 * @param N2f\ApiRequest $request Object including request data and other information.
		 * @param array $matches Optional array of regex matches of URL based off endpoint pattern.
		 */
		public function updateAccount(N2f\ApiRequest $request, array $matches = null) {
			$ret = new N2f\ApiResponse();
			$params = $request->getParameterizedInput();
			$sessRepo = new ApiSessions($this->db, $this->log);
			$account = User::fromParameterHelper($this->db, $params, false, $this->log);

			if ($account->id < 1) {
				$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
				$ret->setData("Invalid account information provided for update");
			} else {
				$session = $sessRepo->getByToken($params->getString(ApiStrings::Field_AuthToken));

				if ($session == null) {
					$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
					$ret->setData("Invalid account information given for update");
				} else {
					$canContinue = true;
					$sessUser = new User($this->db, $session->userId, $this->log);

					if ($session->userId !== $account->id) {
						$roleRepo = new UserRoles($this->db, $this->log);

						if (!$roleRepo->userInRoleByRoleName($sessUser->id, BackendStrings::Admin_Role) || $roleRepo->userInRoleByRoleName($account->id, BackendStrings::Admin_Role)) {
							$canContinue = false;
						}
					}

					$user = new User($this->db, $account->id, $this->log);
					$userRepo = new Users($this->db, $this->log);
					$userRoleRepo = new UserRoles($this->db, $this->log);
					$executingUserIsAdmin = $userRoleRepo->userInRoleByRoleName($sessUser->id, BackendStrings::Admin_Role);

					try {
						if ($canContinue === false) {
							throw new \Exception("Other user edit attempted by non-admin");
						}

						$errorMessage = null;

						if (!$params->hasValue('email') || empty($params->getString('email'))) {
							$account->email = $user->email;
							$account->emailConfirmed = $user->emailConfirmed;
						}

						if ($params->getString('email') !== $user->email) {
							$emailUsers = $userRepo->getByEmail($account->email);

							if ($emailUsers !== null) {
								$canContinue = false;
								$errorMessage = "Failed to update account, email address already exists";
								$this->log->error("Failed to update account for user #{ID}: {VALUE}", array('ID' => $account->id, 'VALUE' => "Duplicate email address requested"));
							} else {
								$account->emailConfirmed = (!$executingUserIsAdmin) ? false : true;
							}
						}

						if ($canContinue && !empty($params->getString('current-password'))) {
							if (!empty($params->getString('new-password')) && !empty($params->getString('new-password-2'))) {
								$curr = $params->getString('current-password');
								$new1 = $params->getString('new-password');
								$new2 = $params->getString('new-password-2');

								if ($new1 === $new2) {
									$loginKey = new LoginKey($this->db, $account->id, LoginKey::PROVIDER_BASIC, $this->log);

									if ($loginKey->provider > LoginKey::PROVIDER_ERROR) {
										if (password_verify($curr, $loginKey->key)) {
											$loginKey->key = password_hash($new1, PASSWORD_DEFAULT);
											$loginKey->update();
										} else {
											$canContinue = false;
											$errorMessage = "Failed to update account, invalid password information supplied";
											$this->log->error("Failed to update account for user #{ID}: {VALUE}", array('ID' => $account->id, 'VALUE' => "Mismatched current password"));
										}
									} else {
										$canContinue = false;
										$errorMessage = "Failed to update account, invalid password information supplied";
										$this->log->error("Failed to update account for user #{ID}: {VALUE}", array('ID' => $account->id, 'VALUE' => "BASIC login key not found"));
									}
								} else {
									$canContinue = false;
									$errorMessage = "Failed to update account, invalid password information supplied";
									$this->log->error("Failed to update account for user #{ID}: {VALUE}", array('ID' => $account->id, 'VALUE' => "Mismatched new passwords"));
								}
							} else {
								$canContinue = false;
								$errorMessage = "Failed to update account, invalid password information supplied";
								$this->log->error("Failed to update account for user #{ID}: {VALUE}", array('ID' => $account->id, 'VALUE' => "Missing new password(s)"));
							}
						} else if ($canContinue && !empty($params->getString('new-password')) && !empty($params->getString('new-password-2'))) {
							if ($executingUserIsAdmin) {
								$new1 = $params->getString('new-password');
								$new2 = $params->getString('new-password-2');

								if ($new1 === $new2) {
									$loginKey = new LoginKey($this->db, $account->id, LoginKey::PROVIDER_BASIC, $this->log);

									if ($loginKey->provider > LoginKey::PROVIDER_ERROR) {
										$loginKey->key = password_hash($new1, PASSWORD_DEFAULT);
										$loginKey->update();
									} else {
										$canContinue = false;
										$errorMessage = "Failed to update account, invalid password information supplied";
										$this->log->error("Failed to update account for user #{ID}: {VALUE}", array('ID' => $account->id, 'VALUE' => "BASIC login key not found"));
									}
								} else {
									$canContinue = false;
									$errorMessage = "Failed to update account, invalid password information supplied";
									$this->log->error("Failed to update account for user #{ID}: {VALUE}", array('ID' => $account->id, 'VALUE' => "Mismatched new passwords"));
								}
							} else {
								$canContinue = false;
								$errorMessage = "Failed to update account, invalid permissions";
								$this->log->error("Failed to update account for user #{ID}: {VALUE}", array('ID' => $account->id, 'VALUE' => "Attempt by non-admin user #{$sessUser->id} to edit user"));
							}
						}

						if (!$canContinue) {
							$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
							$ret->setData($errorMessage);
						} else {
							$account->username = $user->username;
							$account->dateJoined = $user->dateJoined;
							$account->lastLogin = $user->lastLogin;
							$account->update();

							$this->log->info("Updated user #{ID}", array('ID' => $account->id));
							$ret->setHttpCode(N2f\HttpStatusCodes::OK);
							$ret->setData(array(
								'id' => $account->id,
								'email' => $account->email,
								'username' => $account->username,
								'dateJoined' => $account->dateJoined,
								'lastLogin' => $account->lastLogin
							));
						}
					} catch (Exception $ex) {
						$this->log->error("Failed to update account for user #{ID}: {VALUE}", array('ID' => $account->id, 'VALUE' => $ex->getMessage()));
						$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
						$ret->setData("Failed to update account, please contact an administrator");
					}
				}
			}

			return $ret;
		}

		/**
		 * Creates new user account.
		 * 
		 * @param N2f\ApiRequest $request Object including request data and other information.
		 * @param array $matches Optional array of regex matches of URL based off endpoint pattern.
		 */
		public function createAccount(N2f\ApiRequest $request, array $matches = null) {
			$ret = new N2f\ApiResponse();
			$params = $request->getParameterizedInput();
			$sessRepo = new ApiSessions($this->db, $this->log);

			$session = $sessRepo->getByToken($params->getString(ApiStrings::Field_AuthToken));

			if ($params->hasValue('username') && $params->hasValue('email') && $params->hasValue('new-password') && $params->hasValue('new-password-2')) {
				$new1 = $params->getString('new-password');
				$new2 = $params->getString('new-password-2');

				if ($new1 === $new2) {
					if ($session == null) {
						$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
						$ret->setData("Invalid account information given for update");
					} else {
						$user = new User($this->db, null, $this->log);

						try {
							$user->email = $params->getString('email');
							$user->emailConfirmed = true;
							$user->username = $params->getString('username');
							$user->create();

							$key = new LoginKey($this->db, $user->id, LoginKey::PROVIDER_BASIC, $this->log);

							if ($key->key > LoginKey::PROVIDER_ERROR) {
								throw new \Exception("Pre-existing login key found for newly created user");
							}

							$key->userId = $user->id;
							$key->provider = LoginKey::PROVIDER_BASIC;
							$key->key = password_hash($new1, PASSWORD_DEFAULT);
							$key->create();

							$this->log->info("Created user #{ID} with username {USERNAME}", array('ID' => $user->id, 'USERNAME' => $user->username));
							$ret->setHttpCode(N2f\HttpStatusCodes::OK);
							$ret->setData(array(
								'id' => $user->id,
								'email' => $user->email,
								'username' => $user->username,
								'dateJoined' => $user->dateJoined,
								'lastLogin' => $user->lastLogin
							));
						} catch (Exception $ex) {
							$this->log->error("Failed to create account: {VALUE}", array('VALUE' => $ex->getMessage()));
							$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
							$ret->setData("Failed to create account, please contact an administrator");
						}
					}
				} else {
					$this->log->error("Failed to create account: {VALUE}", array('VALUE' => "Invalid password set for account creation"));
					$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
					$ret->setData("Failed to create account, please contact an administrator");
				}
			} else {
				$this->log->error("Failed to create account: {VALUE}", array('VALUE' => "Invalid parameter set for account creation"));
				$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
				$ret->setData("Failed to create account, please contact an administrator");
			}

			return $ret;
		}
	
		/**
		 * Delete a user account (admin only).
		 * 
		 * @param N2f\ApiRequest $request Object including request data and other information.
		 * @param array $matches Optional array of regex matches of URL based off endpoint pattern.
		 */
		public function deleteAccount(N2f\ApiRequest $request, array $matches = null) {
			$ret = new N2f\ApiResponse();
			$params = $request->getParameterizedInput();
			$sessRepo = new ApiSessions($this->db, $this->log);
			$userRoleRepo = new UserRoles($this->db, $this->log);

			$session = $sessRepo->getByToken($params->getString(ApiStrings::Field_AuthToken));

			if ($session === null) {
				$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
				$ret->setData("Failed to delete user, invalid user token");
			} else {
				if (!$params->hasValue('id')) {
					$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
					$ret->setData("Failed to delete user, incomplete parameters");
				} else {
					$executorIsAdmin = $userRoleRepo->userInRoleByRoleName($session->userId, BackendStrings::Admin_Role);
					$executeeIsAdmin = $userRoleRepo->userInRoleByRoleName($params->getInt('id'), BackendStrings::Admin_Role);

					if (!$executorIsAdmin || $executeeIsAdmin) {
						$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
						$ret->setData("Failed to delete user, admin role mismatch");
					} else {
						try {
							$user = new User($this->db, $params->getInt('id'), $this->log);
							$user->delete();

							$ret->setHttpCode(N2f\HttpStatusCodes::OK);
							$ret->setData("User was deleted");
						} catch (Exception $ex) {
							$this->log->error("Failed to delete user #{ID}: {VALUE}", array('ID' => $params->getInt('id'), 'VALUE' => $ex->getMessage()));
							$ret->setHttpCode(N2f\HttpStatusCodes::INTERNAL_SERVER_ERROR);
							$ret->setData("Failed to delete user, please contact an administrator");
						}
					}
				}
			}

			return $ret;
		}
	}

	global $Db, $Log;
	$accountApi = new AccountApi($Db, $Log);

	N2f\ApiHandler::registerEndpoint("GET", "/^account$/i", array($accountApi, 'getAccount'), true);
	N2f\ApiHandler::registerEndpoint("POST", "/^account$/i", array($accountApi, 'updateAccount'), true);
	N2f\ApiHandler::registerEndpoint("PUT", "/^account$/i", array($accountApi, 'createAccount'), BackendStrings::Admin_Role);
	N2f\ApiHandler::registerEndpoint("POST", "/^account\/create$/i", array($accountApi, 'createAccount'), BackendStrings::Admin_Role);
	N2f\ApiHandler::registerEndpoint("DELETE", "/^account$/i", array($accountApi, 'deleteAccount'), BackendStrings::Admin_Role);
	N2f\ApiHandler::registerEndpoint("POST", "/^account\/delete$/i", array($accountApi, 'deleteAccount'), BackendStrings::Admin_Role);
