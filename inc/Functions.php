<?php

	/**
	 * Simplistic method to load libraries via SPL
	 * auto-load.
	 * 
	 * @param string $class The fully-qualified class name.
	 * @return void
	 */
	function doAutoload($class) {
		$file = "~/lib/" . str_replace('\\', '/', $class) . '.php';

		$Io = new N2f\IoHelper(CORE_PATH);

		if ($Io->fileExists($file)) {
			$Io->load($file);
		}

		return;
	}

	/**
	 * Takes a string and performs HTML-aware word-wrapping
	 * with optional length and wrap-string parameters.
	 * 
	 * @param string $string String value to wrap.
	 * @param integer $length Optional integer value of wrap length.
	 * @param string $wrapString Optional string value to use for line endings.
	 * @return string
	 */
	function wordWrapIgnoreHTML($string, $length = 55, $wrapString = "\n") {
		$wrapped = '';
		$word = '';
		$html = false;
		$string = (string) $string;
		$len = strlen($string);

		for ($i = 0; $i < $len; ++$i) {
			$char = $string[$i];

			/** HTML Begins */
			if ($char === '<') {
				if (!empty($word)) {
					$wrapped .= $word;
					$word = '';
				}

				$html = true;
				$wrapped .= $char;
			} else if ($char === '>') {
				$html = false;
				$wrapped .= $char;
			} else if ($html) {
				$wrapped .= $char;
			} else if ($char === ' ' || $char === "\t" || $char === "\n") {
				$wrapped .= $word.$char;
				$word = '';
			} else {
				$word .= $char;

				if (strlen($word) > $length) {
					$wrapped .= $word.$wrapString;
					$word = '';
				}
			}
		}

		if ($word !== '') {
			$wrapped .= $word;
		}

		return $wrapped;
	}

	/**
	 * Performs basic authentication check against $_SESSION
	 * array with \BackendStrings::Session_ApiKey and
	 * \BackendStrings::Session_UserIdKey array indices.
	 * 
	 * @param PDO $db PDO connection resource to use for ApiSession validation.
	 * @param N2f\Logger $log Optional Logger injection, new Logger instance created if not supplied.
	 * @return boolean
	 */
	function isAuthenticated(PDO $db, N2f\Logger $log = null) {
		if (!array_key_exists(BackendStrings::Session_ApiKey, $_SESSION)
			|| !array_key_exists(BackendStrings::Session_UserIdKey, $_SESSION)) {
			return false;
		}

		$apiRepo = new ApiSessions($db, $log);
		$session = $apiRepo->getByToken($_SESSION[BackendStrings::Session_ApiKey]);

		if ($session === null) {
			return false;
		}

		if ($session->userId != $_SESSION[BackendStrings::Session_UserIdKey]) {
			return false;
		}

		return true;
	}

	/**
	 * Returns a ParameterHelper object either from the provided super global array or from
	 * the request input (php://input).
	 * 
	 * @param array $superGlobal The super global (or any old array) to use as the base source.
	 * @return \ParameterHelper A ParameterHelper object to use for working with parameters.
	 */
	function getApiParams(array $superGlobal) {
		$params = new ParameterHelper();

		if (count($superGlobal) > 0) {
			$params = new ParameterHelper($superGlobal);
		} else {
			$body = null;

			try {
				$body = @file_get_contents('php://input');
			} catch (Exception $ex) {
				// nothing atm
			}

			if (stristr($body, '{') !== false) {
				$params = new ParameterHelper(json_decode($body, true));
			}
		}

		return $params;
	}

	/**
	 * Determines if executing environment is
	 * recognized as Windows based.
	 * 
	 * @return bool True if PHP_OS starts with 'WIN', false otherwise.
	 */
	function env_is_windows() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return true;
		}
		
		return false;
	}

	/**
	 * Creates a unique identifier.
	 * 
	 * @param boolean $withBrackets Optional boolean to disable inclusion of brackets, defaults to true.
	 * @return string Unique identifier.
	 */
	function env_get_guid($withBrackets = true) {
		if (function_exists('com_create_guid')) {
			return ($withBrackets === false) ? trim(com_create_guid(), '{}') : com_create_guid();
		} else {
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = (($withBrackets === true) ? chr(123) : "")// "{"
				.substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12)
				.(($withBrackets === true) ? chr(125) : "");// "}"
			return $uuid;
		}
	}

	/**
	 * Parses a collection of arguments into an organized
	 * collection.  Pairs of arguments are put together
	 * while toggle elements (ie, -enable) are given a
	 * value of true.  Case sensitivity can be optionally
	 * disabled.
	 * 
	 * @param array $args Array of arguments to parse.
	 * @param bool $caseInsensitive Optional argument to disable case sensitivity in resulting array.
	 * @return array Array of organized argument values.
	 */
	function env_parse_params(array $args, $caseInsensitive = false) {
		$len = count($args);
		$assoc = array();

		for ($i = 0; $i < $len; ++$i) {
			if ($args[$i][0] == '-' && strlen($args[$i]) > 1) {
				$key = substr($args[$i], ($args[$i][1] == '-') ? 2 : 1);

				if (stripos($key, '=') !== false && strpos($key, '=') != (strlen($key) - 1)) {
					$parts = explode('=', $key, 2);
					$assoc[($caseInsensitive) ? strtolower($parts[0]) : $parts[0]] = $parts[1];
				} else if (stripos($key, '-') !== false && strpos($key, '-') != (strlen($key) - 1)) {
					$parts = explode('-', $key, 2);
					$assoc[($caseInsensitive) ? strtolower($parts[0]) : $parts[0]] = $parts[1];
				} else if (($i + 1) < $len) {
					$assoc[($caseInsensitive) ? strtolower($key) : $key] = ($args[$i + 1][0] != '-') ? $args[++$i] : true;
				} else {
					$assoc[($caseInsensitive) ? strtolower($key) : $key] = true;
				}
			} else {
				if (stripos($args[$i], '=') !== false) {
					$parts = explode('=', $args[$i], 2);
					$assoc[($caseInsensitive) ? strtolower($parts[0]) : $parts[0]] = $parts[1];
				} else {
					$assoc[($caseInsensitive) ? strtolower($args[$i]) : $args[$i]] = true;
				}
			}
		}

		return $assoc;
	}
