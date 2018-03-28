<?php

	namespace N2f;

	/**
	 * ConsoleHelper class to aid with CLI interactions.
	 * 
	 * @version 1.0 
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class ConsoleHelper {
		/**
		 * Collection of arguments.
		 * 
		 * @var array
		 */
		private $argInfo = array();
		/**
		 * Whether or not executing environment
		 * is Windows based.
		 * 
		 * @var bool
		 */
		private $isWindows = false;
		/**
		 * Allows overriding instances to think
		 * they were called from CLI PHP.
		 * 
		 * @var bool
		 */
		private $forceCli = false;
		/**
		 * Attempts to represent the name of the
		 * executed script based on the argv values.
		 * 
		 * @var mixed
		 */
		private $self = null;

		/**
		 * Creates a new ConsoleHelper instance.
		 * 
		 * @param int $argc Number of arguments.
		 * @param array $argv Argument collection.
		 * @param bool $forceCli Force instance to emulate CLI mode.
		 * @return void
		 */
		public function __construct($argc = null, array $argv = null, $forceCli = false) {
			if ($argc === null || $argv === null) {
				$this->argInfo = null;
			} else {
				$this->argInfo['argc'] = $argc;
				$this->argInfo['argv'] = $argv;

				if ($argc > 1) {
					$this->self = array_shift($argv);
					$this->argInfo['arga'] = \env_parse_params($argv);
				} else {
					$this->self = $argv[0];
					$this->argInfo['arga'] = null;
				}
			}

			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$this->isWindows = true;
			}

			$this->forceCli = $forceCli;

			return;
		}

		/**
		 * Compares an argument by key optionally
		 * without case sensitivity. May return
		 * inaccurate results against toggle type
		 * arguments.
		 * 
		 * @param string $key String value of key in argument list.
		 * @param string $value Value to compare against.
		 * @param bool $caseInsensitive Enable case-insensitive comparison.
		 * @return bool True if the values are the same, false otherwise.
		 */
		public function compareArg($key, $value, $caseInsensitive = false) {
			return ($caseInsensitive) ? strtolower($this->argInfo['arga'][$key]) == strtolower($value) : $this->argInfo['arga'] == $value;
		}

		/**
		 * Compares an argument at the given index
		 * optionally without case sensitivity.
		 * Returns false if index is out of bounds.
		 * 
		 * @param int $index Integer value for argument offset.
		 * @param string $value String value to compare against.
		 * @param bool $caseInsensitive Enable case-insensitive comparison.
		 * @return bool True if the values are the same, false otherwise.
		 */
		public function compareArgAt($index, $value, $caseInsensitive = false) {
			return ($this->argInfo['argc'] > $index && (($caseInsensitive) ? strtolower($this->argInfo['argv'][$index]) == strtolower($value) : $this->argInfo['argv'][$index] == $value));
		}

		/**
		 * Retrieves $characters from STDIN.
		 * 
		 * @param int $characters Number of characters to read from STDIN.
		 * @return string|null Trimmed string up to $characters long, or null if $characters is less than 1.
		 */
		public function get($characters = 1) {
			if ($characters < 1) {
				return null;
			}

			return trim(fread(STDIN, $characters));
		}

		/**
		 * Retrieves an entire line from STDIN.
		 * 
		 * @return string Trimmed string from STDIN.
		 */
		public function getLine() {
			return trim(fgets(STDIN));
		}

		/**
		 * Attempts to retrieve an argument by both short and long
		 * versions, otherwise returns a default value that is
		 * optionally provided.
		 * 
		 * @param string $short String value that represents the short name of the parameter.
		 * @param string $long String value that represents the long name of the parameter.
		 * @param mixed $default Default value if parameter not present, set to null if not provided.
		 * @return mixed Either the value of the parameter or the default value.
		 */
		public function getParameterWithDefault($short, $long, $default = null) {
			if ($this->argInfo !== null && array_key_exists('arga', $this->argInfo) && (array_key_exists($short, $this->argInfo['arga']) || array_key_exists($long, $this->argInfo['arga']))) {
				return (array_key_exists($short, $this->argInfo['arga'])) ? $this->argInfo['arga'][$short] : $this->argInfo['arga'][$long];
			}

			return $default;
		}

		/**
		 * Queries a user repeatedly for input.
		 * 
		 * @param string $uery Base prompt, sans-colon.
		 * @param mixed $defaultValue Default value for input, provide null if not present.
		 * @param string $errorMessage Message to display when input not provided correctly.
		 * @param int $maxTries Maximum number of attempts a user can make before the process bails out.
		 * @param callable $validation An optional method or function to provide boolean validation of input.
		 * @param callable $sanitation An optional method or function to provide sanitation of the validated input.
		 * @return \N2f\ReturnHelper A ReturnHelper instance with extra state information.
		 */
		public function getQueriedInput($query, $defaultValue, $errorMessage, $maxTries = 5, $validation = null, $sanitation = null) {
			$Ret = new ReturnHelper();
			$Prompt = $query;

			if ($defaultValue !== null) {
				$Prompt .= " [{$defaultValue}]";
			}

			$Prompt .= ": ";

			if ($validation === null) {
				$validation = function ($Value) { return !empty(trim($Value)); };
			}

			if ($sanitation === null) {
				$sanitation = function ($Value) { return trim($Value); };
			}

			$Attempts = 0;

			while (true) {
				$this->put($Prompt);
				$Val = $this->getLine();

				if (empty($Val) && $defaultValue !== null) {
					$Ret->setGud();
					$Ret->setResult($defaultValue);

					break;
				}

				$valid = $validation($Val);

				if ((!($valid instanceof ReturnHelper) && $valid) || $valid->isGood()) {
					$Sanitized = $sanitation($Val);
					$Ret->setGud();

					if ($Sanitized instanceof ReturnHelper) {
						$Ret = $Sanitized;
					} else {
						$Ret->setResult($Sanitized);
					}

					break;
				} else {
					if ($valid instanceof ReturnHelper && $valid->hasMessages()) {
						$this->putLine($errorMessage . " (" . $valid->getMessages()[0] . ")");
					} else {
						$this->putLine($errorMessage);
					}

					$Attempts++;

					if ($Attempts == $maxTries) {
						$Ret->setMessage("Exceeded maximum number of attempts.");

						break;
					}
				}
			}

			return $Ret;
		}

		/**
		 * Returns the script being called according to the passed
		 * arguments (first argument in $argv).
		 * 
		 * @return mixed String of script name or null if not provided.
		 */
		public function getSelf() {
			return $this->self;
		}

		/**
		 * Checks if the given key exists in the argument list,
		 * optionally without case sensitivity.
		 * 
		 * @param string $key Key name to check in argument list.
		 * @param boolean $caseInsensitive Enable case-insensitive comparison.
		 * @return boolean True if key is found in argument list, false if not.
		 */
		public function hasArg($key, $caseInsensitive = false) {
			if ($this->argInfo['argc'] < 1) {
				return false;
			}

			if ($caseInsensitive) {
				foreach (array_keys($this->argInfo['arga']) as $k) {
					if (strtolower($k) == strtolower($key)) {
						return true;
					}
				}

				return false;
			}

			return array_key_exists($key, $this->argInfo['arga']);
		}

		/**
		 * Checks if the given key exists in the argument list,
		 * using both a short and long version of the key,
		 * optionally without case sensitivity.
		 * 
		 * @param string $short Short version of key name to check in argument list.
		 * @param string $long Long version of key name to check in argument list.
		 * @param boolean $caseInsensitive Enable case-insensitive comparison.
		 * @return boolean True if key is found in argument, false if not.
		 */
		public function hasShortLongArg($short, $long, $caseInsensitive = false) {
			if ($this->argInfo['argc'] < 1) {
				return false;
			}

			if ($caseInsensitive) {
				$lshort = strtolower($short);
				$llong = strtolower($long);

				foreach (array_keys($this->argInfo['arga']) as $k) {
					$lk = strtolower($k);

					if ($lk == $lshort || $lk == $llong) {
						return true;
					}
				}

				return false;
			}

			return array_key_exists($short, $this->argInfo['arga']) || array_key_exists($long, $this->argInfo['arga']);
		}

		/**
		 * Returns whether or not PHP invocation is via CLI
		 * or invocation is emulating CLI.
		 * 
		 * @return bool True if called from CLI or emulating CLI, false otherwise.
		 */
		public function isCLI() {
			return $this->forceCli || php_sapi_name() == 'cli';
		}

		/**
		 * Returns whether or not PHP invocation is via CLI
		 * and ignores forced CLI mode.
		 * 
		 * @return bool True if called from CLI, false otherwise.
		 */
		public function isNaturalCLI() {
			return php_sapi_name() == 'cli';
		}

		/**
		 * Returns the number of arguments.
		 * 
		 * @return int Number of arguments supplied to the instance.
		 */
		public function numArgs() {
			return $this->argInfo['argc'];
		}

		/**
		 * Returns the argument collection, either
		 * as-received by the instance or as an
		 * associative array.
		 * 
		 * @param bool $AsAssociative Enables returning list as an associative array.
		 * @return array Associative or regular array of argument list.
		 */
		public function parameters($asAssociative = false) {
			if ($this->argInfo === null) {
				return null;
			}

			return ($asAssociative) ? $this->argInfo['arga'] : $this->argInfo['argv'];
		}

		/**
		 * Outputs the buffer to STDIN.
		 * 
		 * @param string $buf Buffer to output.
		 * @return void
		 */
		public function put($buf) {
			echo($buf);

			return;
		}

		/**
		 * Outputs the buffer followed by a newline
		 * to STDIN.
		 * 
		 * @param string $buf Buffer to output.
		 * @return void
		 */
		public function putLine($buf = null) {
			if ($buf !== null) {
				echo($buf);
			}

			echo("\n");

			return;
		}
	}
