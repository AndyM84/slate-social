<?php

	namespace N2f;

	/**
	 * Abstract class to represent an Enum base type.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	abstract class Enum {
		/**
		 * Cached collection of class constants.
		 * 
		 * @var array
		 */
		private static $constCache = null;
		/**
		 * Stored value for enum.
		 * 
		 * @var integer
		 */
		private $value = null;

		/**
		 * Initializes the enum instance with a value.
		 * 
		 * @param integer $value Integer value from class constant (enum).
		 */
		public function __construct($value) {
			$this->value = $value;

			return;
		}

		/**
		 * Retrieves list of constants for the class.
		 * 
		 * @return array Array of class constants (enum names).
		 */
		public static function getConstList() {
			if (self::$constCache === null) {
				self::$constCache = array();
			}

			$cclass = get_called_class();

			if (!array_key_exists($cclass, self::$constCache)) {
				$ref = new \ReflectionClass($cclass);
				self::$constCache[$cclass] = $ref->getConstants();
			}

			return self::$constCache[$cclass];
		}

		/**
		 * Determines if the stored value is equal to the supplied value.
		 * 
		 * @param integer $value Value to compare against stored value.
		 * @return boolean True or false based on value comparison, also false if no value stored.
		 */
		public function is($value) {
			if ($this->value === null || $this->value !== $value) {
				return false;
			}

			return true;
		}
	}
