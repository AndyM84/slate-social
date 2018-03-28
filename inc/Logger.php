<?php

	namespace N2f;

	// Log levels
	define('N2F_LOG_NONE',      0);
	define('N2F_LOG_DEBUG',     1);
	define('N2F_LOG_INFO',      2);
	define('N2F_LOG_NOTICE',    4);
	define('N2F_LOG_WARNING',   8);
	define('N2F_LOG_ERROR',     16);
	define('N2F_LOG_CRITICAL',  32);
	define('N2F_LOG_ALERT',     64);
	define('N2F_LOG_EMERGENCY', 128);
	define('N2F_LOG_ALL',       255);

	/**
	 * Class for managing Logger configuration.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class LoggerConfig {
		/**
		 * Whether or not logs should be dumped.
		 * 
		 * @var bool
		 */
		public $dumpLogs;
		/**
		 * The current log level for this instance.
		 * 
		 * @var mixed
		 */
		public $logLevel;

		/**
		 * Creates a new LoggerConfig instance.
		 * 
		 * @param array $config Optional array of configuration values.
		 * @return void
		 */
		public function __construct(array $config = null) {
			if ($config === null || count($config) < 1) {
				$this->dumpLogs = false;
				$this->logLevel = N2F_LOG_ERROR;
			} else {
				$this->dumpLogs = (array_key_exists('dump_logs', $config) && $config['dump_logs']) ? true : false;
				$this->logLevel = (array_key_exists('log_level', $config)) ? $config['log_level'] : N2F_LOG_ERROR;
			}

			return;
		}
	}

	/**
	* Basic logger class with notification chains.
	*
	* @version 1.0
	* @author Andrew Male (AndyM84)
	* @package N2f
	*/
	class Logger {
		/**
		 * Local instance of LoggerConfig.
		 * 
		 * @var \N2f\LoggerConfig
		 */
		private $config;
		/**
		 * Collection of various log level buckets.
		 * 
		 * @var array
		 */
		private $logs = array(
			'Debug' => array(),
			'Info' => array(),
			'Notice' => array(),
			'Warning' => array(),
			'Error' => array(),
			'Critical' => array(),
			'Alert' => array(),
			'Emergency' => array()
		);
		private $logLevels = array(
			N2F_LOG_DEBUG => 'Debug',
			N2F_LOG_INFO => 'Info',
			N2F_LOG_NOTICE => 'Notice',
			N2F_LOG_WARNING => 'Warning',
			N2F_LOG_ERROR => 'Error',
			N2F_LOG_CRITICAL => 'Critical',
			N2F_LOG_ALERT => 'Alert',
			N2F_LOG_EMERGENCY => 'Emergency'
		);

		/**
		 * Creates a new Logger instance.
		 * 
		 * @param mixed $config Optional array or LoggerConfig instance with configuration settings.
		 * @return void
		 */
		public function __construct($config = null) {
			if ($config !== null) {
				if (is_array($config)) {
					$this->config = new LoggerConfig($config);
				} else if ($config instanceof LoggerConfig) {
					$this->config = clone $config;
				} else {
					$this->config = new LoggerConfig();
				}
			} else {
				$this->config = new LoggerConfig();
			}

			return;
		}

		/**
		 * Adds an ALERT level log entry with optional
		 * context for keyword replacements.
		 * 
		 * @param string $message String value of log message.
		 * @param array $context Optional array value for context variables.
		 * @return \N2f\Logger The current Logger instance.
		 */
		public function alert($message, array $context = array()) {
			return $this->log(N2F_LOG_ALERT, $message, $context);
		}

		/**
		 * Convert a log level to a short or long string
		 * display name.
		 * 
		 * @param int $level Log level (integer) to convert.
		 * @param bool $short Whether or not to use short display name.
		 * @return string String representation of log level.
		 */
		public function convertLevelToString($level, $short = false) {
			$ret = '';

			switch ($level) {
				case N2F_LOG_DEBUG:
					$ret = ($short === true) ? 'DBG' : 'DEBUG';

					break;
				case N2F_LOG_INFO:
					$ret = ($short === true) ? 'INF' : 'INFO';

					break;
				case N2F_LOG_NOTICE:
					$ret = ($short === true) ? 'NOT' : 'NOTICE';

					break;
				case N2F_LOG_WARNING:
					$ret = ($short === true) ? 'WAR' : 'WARNING';

					break;
				case N2F_LOG_ERROR:
					$ret = ($short === true) ? 'ERR' : 'ERROR';

					break;
				case N2F_LOG_CRITICAL:
					$ret = ($short === true) ? 'CRT' : 'CRITICAL';

					break;
				case N2F_LOG_ALERT:
					$ret = ($short === true) ? 'ALR' : 'ALERT';

					break;
				case N2F_LOG_EMERGENCY:
					$ret = ($short === true) ? 'EMR' : 'EMERGENCY';

					break;
				default:
					$ret = 'N/A';

					break;
			}

			return $ret;
		}

		/**
		 * Adds a CRITICAL level log entry with optional
		 * context for keyword replacements.
		 * 
		 * @param string $message String value of log message.
		 * @param array $context Optional array value for context variables.
		 * @return \N2f\Logger The current Logger instance.
		 */
		public function critical($message, array $context = array()) {
			return $this->log(N2F_LOG_CRITICAL, $message, $context);
		}

		/**
		 * Adds a DEBUG level log entry with optional
		 * context for keyword replacements.
		 * 
		 * @param string $message String value of log message.
		 * @param array $context Optional array value for context variables.
		 * @return \N2f\Logger The current Logger instance.
		 */
		public function debug($message, array $context = array()) {
			return $this->log(N2F_LOG_DEBUG, $message, $context);
		}

		/**
		 * Adds an EMERGENCY level log entry with optional
		 * context for keyword replacements.
		 * 
		 * @param string $message String value of log message.
		 * @param array $context Optional array value for context variables.
		 * @return \N2f\Logger The current Logger instance.
		 */
		public function emergency($message, array $context = array()) {
			return $this->log(N2F_LOG_EMERGENCY, $message, $context);
		}

		/**
		 * Adds an ERROR level log entry with optional
		 * context for keyword replacements.
		 * 
		 * @param string $message String value of log message.
		 * @param array $context Optional array value for context variables.
		 * @return \N2f\Logger The current Logger instance.
		 */
		public function error($message, array $context = array()) {
			return $this->log(N2F_LOG_ERROR, $message, $context);
		}

		/**
		 * Returns the current log level for the instance.
		 * 
		 * @return int The current log level flag.
		 */
		public function getLogLevel() {
			return $this->config->logLevel;
		}

		/**
		 * Returns a collection of log entries based
		 * on the provided level.
		 * 
		 * @param int $level Flag for log level(s) to return.
		 * @return array|null Array of log entries or null if invalid flag.
		 */
		public function getLogs($level = null) {
			if ($level === null) {
				return $this->logs;
			}

			if ($this->validSingleLevel($level)) {
				$Ret = array();

				foreach ($this->logLevels as $lvl => $key) {
					if ($level & $lvl) {
						$Ret[$key] = $this->logs[$key];
					}
				}

				return $Ret;
			}

			return null;
		}

		/**
		 * Adds an INFO level log entry with optional
		 * context for keyword replacements.
		 * 
		 * @param string $message String value of log message.
		 * @param array $context Optional array value for context variables.
		 * @return \N2f\Logger The current Logger instance.
		 */
		public function info($message, array $context = array()) {
			return $this->log(N2F_LOG_INFO, $message, $context);
		}

		/**
		 * Check if a log level flag is included
		 * in the current setting.
		 * 
		 * @param int $level Flag to check against current level(s).
		 * @return int Whether or not the flags are enabled.
		 */
		public function isLevelLogged($level) {
			if (!is_int($level)) {
				$level = $this->levelFromString($level);
			}

			return $this->config->logLevel & $level;
		}

		/**
		 * Converts a string representation of log levels
		 * (such as 'N2F_LOG_DEBUG | N2F_LOG_INFO') into
		 * the appropriate integer flag.
		 * 
		 * @param string $level String representation of level flag.
		 * @return int $Level converted to the integer flag.
		 */
		protected function levelFromString($level) {
			if ($level === null || empty($level)) {
				return N2F_LOG_NONE;
			}

			$Ret = 0;
			$Parts = explode('|', $level);

			foreach (array_values($Parts) as $Part) {
				$Part = trim($Part);

				if ($Part == 'N2F_LOG_ALL') {
					$Ret = N2F_LOG_ALL;

					break;
				}

				switch ($Part) {
					case 'N2F_LOG_DEBUG':
						$Ret = $Ret | N2F_LOG_DEBUG;

						break;
					case 'N2F_LOG_INFO':
						$Ret = $Ret | N2F_LOG_INFO;

						break;
					case 'N2F_LOG_NOTICE':
						$Ret = $Ret | N2F_LOG_NOTICE;

						break;
					case 'N2F_LOG_WARNING':
						$Ret = $Ret | N2F_LOG_WARNING;

						break;
					case 'N2F_LOG_ERROR':
						$Ret = $Ret | N2F_LOG_ERROR;

						break;
					case 'N2F_LOG_CRITICAL':
						$Ret = $Ret | N2F_LOG_CRITICAL;

						break;
					case 'N2F_LOG_ALERT':
						$Ret = $Ret | N2F_LOG_ALERT;

						break;
					case 'N2F_LOG_EMERGENCY':
						$Ret = $Ret | N2F_LOG_EMERGENCY;

						break;
				}
			}

			return $Ret;
		}

		/**
		 * Adds a log entry with optional context
		 * for keyword replacements.
		 * 
		 * @param int $level Integer level flag of log entry.
		 * @param string $message String value of log message.
		 * @param array $context Optional array for context variables.
		 * @return \N2f\Logger The current Logger instance.
		 */
		protected function log($level, $message, array $context = null) {
			if ($context === null) {
				$context = array();
			}

			if (!is_int($level)) {
				$level = $this->levelFromString($level);
			}

			if (count($context) > 0) {
				$Replace = array();

				foreach ($context as $key => $val) {
					$Replace['{'.$key.'}'] = $val;
				}

				$message = str_replace(array_keys($Replace), array_values($Replace), $message);
			}

			if ($this->ValidSingleLevel($level)) {
				$ts = new \DateTime("now", new \DateTimeZone("UTC"));

				$Log = array(
					'Time' => $ts->getTimestamp(),
					'Timestamp' => $ts->format("Y-m-d G:i:s T"),
					'Message' => $message,
					'Level' => $level
				);

				foreach ($this->logLevels as $Lvl => $Key) {
					if ($level & $Lvl) {
						$this->logs[$Key][] = $Log;

						break;
					}
				}
			}

			return $this;
		}

		/**
		 * Adds a NOTICE level log entry with optional
		 * context for keyword replacements.
		 * 
		 * @param string $message String value of log message.
		 * @param array $context Optional array for context variables.
		 * @return \N2f\Logger The current Logger instance.
		 */
		public function notice($message, array $context = array()) {
			return $this->log(N2F_LOG_NOTICE, $message, $context);
		}

		/**
		 * Outputs log data to standard out, either as plain text
		 * or with HTML structure.
		 * 
		 * @param bool Whether or not to output as HTML.
		 * @return void
		 */
		public function output($asHtml = false) {
			if ($asHtml === true) {
				echo("<div>");
			}

			foreach (array_values($this->unrollLogs()) as $Log) {
				$lvl = $this->convertLevelToString($Log['Level'], true);
				$msg = "[{$Log['Timestamp']} - {$lvl}] {$Log['Message']}";

				if ($asHtml === true) {
					echo("<div>{$msg}</div>");
				} else {
					echo($msg);
				}
			}

			if ($asHtml === true) {
				echo("</div>");
			}

			return;
		}

		/**
		 * Outputs log data to the specified file.
		 * 
		 * @param string $fileName String value of filename for output destination.
		 */
		public function outputToFile($fileName = null) {
			$data = '';

			foreach (array_values($this->unrollLogs()) as $Log) {
				if ($data != '') {
					$data .= "\n";
				}

				$lvl = $this->convertLevelToString($Log['Level'], true);
				$data .= "[{$Log['Timestamp']} - {$lvl}] {$Log['Message']}";
			}

			if (file_exists($fileName) && filesize($fileName) > 0) {
				$data = "\n" . $data;
			}

			file_put_contents($fileName, $data, FILE_APPEND);

			return;
		}

		/**
		 * Unrolls all log messages into a chronological
		 * single-dimension array.
		 * 
		 * @return array
		 */
		public function unrollLogs() {
			$unrolled = array();

			foreach (array_values($this->logs) as $stack) {
				$unrolled = array_merge($unrolled, $stack);
			}

			usort($unrolled, function ($a, $b) {
				if ($a['Time'] == $b['Time']) {
					return 0;
				}

				return ($a['Time'] < $b['Time']) ? -1 : 1;
			});

			return $unrolled;
		}

		/**
		 * Adds a WARNING level log entry with optional
		 * context for keyword replacements.
		 * 
		 * @param string $message String value of log message.
		 * @param array $context Optional array for context variables.
		 * @return \N2f\Logger The current Logger instance.
		 */
		public function warning($message, array $context = null) {
			return $this->log(N2F_LOG_WARNING, $message, $context);
		}

		/**
		 * Determines whether or not a level (string or integer)
		 * is valid.
		 * 
		 * @param mixed $level String or integer value of log level.
		 * @return bool True if level is one or more valid levels.
		 */
		public static function validLevel($level) {
			if (is_int($level)) {
				if ($level > N2F_LOG_ALL || $level < 0) {
					return false;
				}

				return true;
			}

			if (!is_string($level)) {
				return false;
			}

			$Parts = explode('|', $level);

			if (count($Parts) < 1) {
				return false;
			}

			foreach (array_values($Parts) as $Part) {
				$Part = trim($Part);

				switch ($Part) {
					case 'N2F_LOG_NONE':
					case 'N2F_LOG_DEBUG':
					case 'N2F_LOG_INFO':
					case 'N2F_LOG_NOTICE':
					case 'N2F_LOG_WARNING':
					case 'N2F_LOG_ERROR':
					case 'N2F_LOG_CRITICAL':
					case 'N2F_LOG_ALERT':
					case 'N2F_LOG_EMERGENCY':
					case 'N2F_LOG_ALL':
						break;
					default:
						return false;
				}
			}

			return true;
		}

		/**
		 * Determines whether or not a single level
		 * (string or integer) is valid.
		 * 
		 * @param mixed $level String or integer value of a single log level.
		 * @return bool True if level is valid.
		 */
		public static function validSingleLevel($level) {
			if (is_int($level)) {
				switch ($level) {
					case N2F_LOG_NONE:
					case N2F_LOG_DEBUG:
					case N2F_LOG_INFO:
					case N2F_LOG_NOTICE:
					case N2F_LOG_WARNING:
					case N2F_LOG_ERROR:
					case N2F_LOG_CRITICAL:
					case N2F_LOG_ALERT:
					case N2F_LOG_EMERGENCY:
					case N2F_LOG_ALL:
						break;
					default:
						return false;
				}

				return true;
			}

			if (!is_string($level)) {
				return false;
			}

			switch (trim($level)) {
				case 'N2F_LOG_NONE':
				case 'N2F_LOG_DEBUG':
				case 'N2F_LOG_INFO':
				case 'N2F_LOG_NOTICE':
				case 'N2F_LOG_WARNING':
				case 'N2F_LOG_ERROR':
				case 'N2F_LOG_CRITICAL':
				case 'N2F_LOG_ALERT':
				case 'N2F_LOG_EMERGENCY':
				case 'N2F_LOG_ALL':
					break;
				default:
					return false;
			}

			return true;
		}
	}
