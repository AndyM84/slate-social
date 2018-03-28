<?php

	/**
	 * Base data layer class with generic
	 * methods for simple DL models.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class BaseClass {
		/**
		 * Internal PDO instance.
		 * 
		 * @var PDO
		 */
		protected $db = null;
		/**
		 * Internal N2f\Logger instance.
		 * 
		 * @var N2f\Logger
		 */
		protected $log = null;


		/**
		 * Array-based instantiation for DL model objects.
		 * 
		 * @param PDO $db PDO connection resource for use by resulting object.
		 * @param array $source Array to attempt using as object property values.
		 * @throws Exception Thrown if given an empty or null array, base count mismatch between properties and array elements, or if an object property is missing from the array.
		 * @return object
		 */
		public static function fromArray(PDO $db, array $source) {
			$className = get_called_class();

			if ($source === null || count($source) < 1) {
				throw new Exception("Cannot populate {$className} from empty or non-existent array");
			}

			$properties = get_class_vars(get_called_class());

			if ((count($properties) - 2) != count($source)) {
				throw new Exception("Cannot populate {$className} from array, variable count mismatch (" . count($properties) . " vs " . count($source) . ")");
			}

			$ret = new $className($db);

			foreach ($source as $key => $val) {
				$keyFound = false;

				foreach (array_keys($properties) as $prop) {
					if (strtolower($prop) == strtolower($key)) {
						$keyFound = true;
						$ret->{$prop} = $val;

						break;
					}
				}

				if ($keyFound === false) {
					throw new Exception("Couldn't find match for {$key} index while populating {$className}");
				}
			}

			return $ret;
		}

		/**
		 * Instantiates a base class with the PDO and Logger
		 * dependency injections.
		 * 
		 * @param PDO $db PDO connection resource for use by object.
		 * @param N2f\Logger $log Optional Logger injection, new Logger instance created if not supplied.
		 * @throws Exception Thrown if PDO resource is null or not setup correctly.
		 */
		public function __construct(PDO $db, N2f\Logger $log = null) {
			$this->db = $db;
			$this->log = ($log !== null) ? $log : new N2f\Logger();

			if ($this->db === null || !$this->db) {
				throw new Exception(ErrorStrings::DB_Not_Initialized);
			}

			return;
		}

		/**
		 * Produce generic log message and generate Exception
		 * based on status of an identifier comparison.
		 * 
		 * @param boolean $evaluatesTo Evaluated identifier comparison.
		 * @param string $methodName Name of method producing assertion on called class.
		 * @param string $identifierName Name of the identifier being validated.
		 * @throws Exception Thrown if $evaluatesTo is false.
		 */
		protected function assertIdentifier($evaluatesTo, $methodName, $identifierName) {
			if ($evaluatesTo === true) {
				return;
			}

			$called_class = get_called_class();
			$this->log->error("Error in {$called_class}::{$methodName}: Called without a valid {$identifierName} identifier");

			throw new Exception("Cannot call {$called_class}::{$methodName} without valid {$identifierName} identifier");
		}
	}