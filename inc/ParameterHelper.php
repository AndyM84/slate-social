<?php

	namespace N2f;

	/**
	 * Class to give basic type casting for parameters.
	 *
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class ParameterHelper {
		/**
		 * Array of parameters.
		 * 
		 * @var array
		 */
		private $parameters = array();
		/**
		 * Whether or not the instance is valid.
		 * 
		 * @var bool
		 */
		private $isValid = false;


		/**
		 * Creates a new ParameterHelper instance.
		 * 
		 * @param array $params Array of parameters to dispense.
		 * @return void
		 */
		public function __construct(array $params = null) {
			if ($params === null || count($params) < 1) {
				return;
			}

			$this->parameters = $params;
			$this->isValid = true;

			return;
		}

		/**
		 * Returns the number of values in the parameter list.
		 * 
		 * @return int Number of parameters.
		 */
		public function numValues() {
			return count($this->parameters);
		}

		/**
		 * Check if a value exists within the parameter list.
		 * 
		 * @param string $key String value of key to compare against.
		 * @param bool $canBeEmpty True if the value is allowed to be empty.
		 * @return bool True if key exists in parameter list, false otherwise.
		 */
		public function hasValue($key, $canBeEmpty = true) {
			if ($canBeEmpty) {
				return array_key_exists($key, $this->parameters);
			}

			return array_key_exists($key, $this->parameters) && !empty($this->parameters[$key]);
		}

		/**
		 * Returns a parameter cast as an integer.
		 * 
		 * @param string $key String value of key to retrieve.
		 * @param int $default Optional default value.
		 * @return int Integer value of key or default value if not present.
		 */
		public function getInt($key, $default = null) {
			if (!$this->isValid || !array_key_exists($key, $this->parameters)) {
				return $default;
			}

			return intval($this->parameters[$key]);
		}

		/**
		 * Returns a parameter cast as a float.
		 * 
		 * @param string $key String value of key to retrieve.
		 * @param float $default Optional default value.
		 * @return float Float value of key or default value if not present.
		 */
		public function getFloat($key, $default = null) {
			if (!$this->isValid || !array_key_exists($key, $this->parameters)) {
				return $default;
			}

			return floatval($this->parameters[$key]);
		}

		/**
		 * Returns a parameter cast as a double.
		 * 
		 * @param string $key String value of key to retrieve.
		 * @param double $default Optional default value.
		 * @return double Double value of key or default value if not present.
		 */
		public function getDouble($key, $default = null) {
			if (!$this->isValid || !array_key_exists($key, $this->parameters)) {
				return $default;
			}

			return doubleval($this->parameters[$key]);
		}

		/**
		 * Returns a parameter cast as a bool.
		 * 
		 * @param string $key String value of key to retrieve.
		 * @param bool $default Optional default value.
		 * @return bool Bool value of key or default value if not present.
		 */
		public function getBool($key, $default = null) {
			if (!$this->isValid || !array_key_exists($key, $this->parameters)) {
				return $default;
			}

			return boolval($this->parameters[$key]);
		}

		/**
		 * Returns a parameter cast as decoded JSON
		 * data.
		 * 
		 * @param string $key String value of key to retrieve.
		 * @param bool $asArray Toggle returning as an array.
		 * @param mixed $default Optional default value.
		 * @return mixed Mixed value of key or default value if not present.
		 */
		public function getJson($key, $asArray = false, $default = null) {
			if (!$this->isValid || !array_key_exists($key, $this->parameters)) {
				return $default;
			}

			return json_decode($this->parameters[$key], $asArray);
		}

		/**
		 * Returns a parameter cast as a string.
		 * 
		 * @param string $key String value of key to retrieve.
		 * @param string $default Optional default value.
		 * @return string String value of key or default value if not present.
		 */
		public function getString($key, $default = null) {
			if (!$this->isValid || !array_key_exists($key, $this->parameters)) {
				return $default;
			}

			return strval($this->parameters[$key]);
		}

		/**
		 * Returns a raw parameter value.
		 * 
		 * @param string $key String value of key to retrieve.
		 * @param mixed $default Optional default value.
		 * @return mixed Mixed value of key or default value if not present.
		 */
		public function getRaw($key, $default = null) {
			if (!$this->isValid || ($key !== null && !array_key_exists($key, $this->parameters))) {
				return $default;
			}

			if ($key === null) {
				return $this->parameters;
			}

			return $this->parameters[$key];
		}
	}