<?php

	namespace N2f;

	/**
	 * Class to orchestrate API request handling.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class ApiHandler {
		/**
		 * Static instance of overall default
		 * endpoint (no match for URL or
		 * request type).
		 * 
		 * @var mixed
		 */
		private static $defaultEndpoint = null;
		/**
		 * Static collection of endpoints.
		 * 
		 * @var array
		 */
		private static $endpoints = array();
		/**
		 * Static singleton instance for
		 * handler.
		 * 
		 * @var ApiHandler
		 */
		private static $instance = null;
		/**
		 * Internal instance of current
		 * request.
		 * 
		 * @var ApiRequest
		 */
		private $request = null;
		/**
		 * Internal Logger instance for object.
		 * 
		 * @var Logger
		 */
		private $log = null;
		/**
		 * Internal PDO connection resource for
		 * object.
		 * 
		 * @var \PDO
		 */
		private $db = null;


		/**
		 * Retrieves the singleton ApiHandler
		 * instance.
		 * 
		 * @param \PDO $db PDO connection resource for object.
		 * @param Logger $log Optional Logger instance, new instance created if not provided.
		 * @return ApiHandler
		 */
		public static function getInstance(\PDO $db, Logger $log = null) {
			if (self::$instance === null) {
				self::$instance = new ApiHandler($db, ($log === null) ? new Logger() : $log);
			}

			return self::$instance;
		}

		/**
		 * Adds an endpoint callback to the
		 * internal collection.
		 * 
		 * @param array|string $verbs String (or array of string) value(s) of applicable request verbs for endpoint.
		 * @param string $pattern String value of URL pattern for endpoint.
		 * @param callable $callback Actual endpoint callback for use when matched.
		 * @param mixed $authRoles Optional string or array of string values representing UserRoles for endpoint access control.
		 */
		public static function registerEndpoint($verbs, $pattern, callable $callback, $authRoles = null) {
			if ($pattern === null) {
				self::$defaultEndpoint = $callback;

				return;
			}

			$sverbs = self::splitVerbs($verbs);

			foreach (array_values($sverbs) as $v) {
				if (!array_key_exists($v, self::$endpoints)) {
					self::$endpoints[$v] = array();
				}

				if (!array_key_exists($pattern, self::$endpoints[$v])) {
					self::$endpoints[$v][$pattern] = array(
						\ApiStrings::Index_Callback => $callback,
						\ApiStrings::Index_AuthRoles => $authRoles
					);
				}
			}

			return;
		}

		/**
		 * Set and send the HTTP response code
		 * for the request.
		 * 
		 * @param integer $code Integer value of response code.
		 */
		public static function setHttpResponseCode($code) {
			if (!function_exists('http_response_code')) {
				header('X-PHP-Response-Code: ' . $code, true, $code);
			} else {
				http_response_code($code);
			}

			return;
		}

		/**
		 * Internal method to return an array of
		 * verbs given a pipe-delimited (|)
		 * string. The '*' will return all verbs.
		 * 
		 * @param string $verbs String value to split into verb array.
		 * @return array
		 */
		protected static function splitVerbs($verbs) {
			if ($verbs == '*') {
				return array(
					'DELETE',
					'ERROR',
					'GET',
					'HEAD',
					'OPTIONS',
					'POST',
					'PUT'
				);
			}

			return explode('|', $verbs);
		}

		/**
		 * Internal ctor for singleton instantiation,
		 * assigns the ApiRequest.
		 * 
		 * @param \PDO $db PDO connection resource for object.
		 * @param Logger $log Logger instance for object.
		 */
		protected function __construct(\PDO $db, Logger $log) {
			$this->db = $db;
			$this->log = $log;
			$this->request = new ApiRequest();

			return;
		}

		/**
		 * Attempts to handle the APi request, outputting several API-specific
		 * headers (Content-Type, Access-Control-Allow-Origin, and Cache-Control).
		 * Optionally allows for overriding the URL parameter in case a change
		 * is made to the .htaccess rules.
		 * 
		 * @param string $urlParam Optional string value to change parameter for URL from .htaccess rewrites, defaults to 'url'.
		 */
		public function handle($urlParam = 'url') {
			if ($urlParam === null || empty($urlParam)) {
				return;
			}

			header('Content-Type: application/json');
			header('Access-Control-Allow-Origin: *');
			header('Cache-Control: max-age=500');

			$get = $this->request->getParameterizedGet();

			if (!$get->hasValue($urlParam)) {
				if (self::$defaultEndpoint !== null) {
					call_user_func(self::$defaultEndpoint, $this->request, array());

					return;
				}
				
				self::setHttpResponseCode(HttpStatusCodes::NOT_FOUND);
				echo(json_encode("Invalid URL"));

				return;
			}

			$url = $get->getString($urlParam);
			$handled = false;
			$out = null;

			if (array_key_exists($this->request->getRequestTypeName(), self::$endpoints) !== false) {
				$vendpoints = self::$endpoints[$this->request->getRequestTypeName()];

				foreach ($vendpoints as $pattern => $data) {
					if (preg_match($pattern, $url, $matches, PREG_OFFSET_CAPTURE) === 1) {
						$callback = $data[\ApiStrings::Index_Callback];
						$authRoles = $data[\ApiStrings::Index_AuthRoles];

						if ($authRoles !== null) {
							if (!$this->request->getParameterizedInput()->hasValue(\ApiStrings::Field_AuthToken)) {
								self::setHttpResponseCode(HttpStatusCodes::FORBIDDEN);
								echo(json_encode("No authentication parameter provided for auth-only endpoint"));

								return;
							}

							$sessRepo = new \ApiSessions($this->db, $this->log);
							$userRolesRepo = new \UserRoles($this->db, $this->log);
							$session = $sessRepo->getByToken($this->request->getParameterizedInput()->getString(\ApiStrings::Field_AuthToken));

							if ($session === null) {
								self::setHttpResponseCode(HttpStatusCodes::FORBIDDEN);
								echo(json_encode("Invalid authentication parameter provided for auth-only endpoint"));

								return;
							}

							if ($authRoles !== true) {
								if (is_string($authRoles)) {
									$authRoles = array($authRoles);
								}

								$hasRole = false;

								foreach (array_values($authRoles) as $role) {
									if ($userRolesRepo->userInRoleByRoleName($session->userId, $role)) {
										$hasRole = true;

										break;
									}
								}

								if ($hasRole === false) {
									self::setHttpResponseCode(HttpStatusCodes::FORBIDDEN);
									echo(json_encode("Unauthorized access for auth-only endpoint"));

									return;
								}
							}
						}

						ob_start();
						$out = call_user_func($callback, $this->request, $matches);
						$handled = true;
						ob_end_clean();

						break;
					}
				}
			}

			if ($handled === false) {
				if (self::$defaultEndpoint !== null) {
					call_user_func(self::$defaultEndpoint, $this->request, array());

					return;
				}
				
				self::setHttpResponseCode(HttpStatusCodes::NOT_FOUND);
				echo(json_encode("Invalid URL"));

				return;
			}

			if ($out !== null) {
				if ($out instanceof ApiResponse) {
					self::setHttpResponseCode($out->getHttpCode());
					echo(json_encode($out->getData()));
				} else {
					echo($out);
				}
			}

			return;
		}
	}
