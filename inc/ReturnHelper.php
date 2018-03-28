<?php

	namespace N2f;

	/**
	 * Class to make for more descriptive return values.
	 *
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class ReturnHelper {
		/**
		 * Collection of messages for instance.
		 * 
		 * @var array
		 */
		private $messages;
		/**
		 * Collection of results for instance.
		 * 
		 * @var array
		 */
		private $results;
		/**
		 * Current status for instance. Default
		 * is BAD.
		 * 
		 * @var int
		 */
		private $status;

		const BAD = 0;
		const GUD = 1;


		/**
		 * Creates a new ReturnHelper instance. Defaults
		 * status to BAD.
		 */
		public function __construct() {
			$this->messages = array();
			$this->results = array();
			$this->status = self::BAD;

			return;
		}

		/**
		 * Returns whether or not the status is bad.
		 * 
		 * @return bool True if status is BAD, false otherwise.
		 */
		public function isBad() {
			return !$this->status;
		}

		/**
		 * Alias of IsGud() for those with stricter
		 * english requirements.
		 * 
		 * @return bool True if status is GUD, false otherwise.
		 */
		public function isGood() {
			return $this->IsGud();
		}

		/**
		 * Returns whether or not the status is good.
		 * 
		 * @return bool True if status is GUD, false otherwise.
		 */
		public function isGud() {
			return $this->status === self::GUD;
		}

		/**
		 * Returns any messages the instance contains.
		 * 
		 * @return array|null Array of messages if available, null otherwise.
		 */
		public function getMessages() {
			if (count($this->messages) < 1) {
				return null;
			}

			return $this->messages;
		}

		/**
		 * Returns any results the instance contains.
		 * 
		 * @return mixed|null Results if available, null otherwise.
		 */
		public function getResults() {
			if (count($this->results) < 1) {
				return null;
			} else if (count($this->results) == 1) {
				return $this->results[0];
			}

			return $this->results;
		}

		/**
		 * Returns whether or not the instance contains
		 * messages.
		 * 
		 * @return bool True if messages are present, false otherwise.
		 */
		public function hasMessages() {
			return count($this->messages) > 0;
		}

		/**
		 * Adds a message to the instance.
		 * 
		 * @param string $message String value of message to add.
		 * @return \N2f\ReturnHelper The current ReturnHelper instance.
		 */
		public function setMessage($message) {
			$this->messages[] = $message;

			return $this;
		}

		/**
		 * Adds multiple messages to the instance.
		 * 
		 * @param array $messages Array of string values to add.
		 * @return \N2f\ReturnHelper The current ReturnHelper instance.
		 */
		public function setMessages(array $messages) {
			if (count($messages) < 1) {
				return $this;
			}

			foreach (array_values($messages) as $Msg) {
				$this->messages[] = $Msg;
			}

			return $this;
		}

		/**
		 * Adds a result to the instance.
		 * 
		 * @param mixed $result Result to add to instance.
		 * @return \N2f\ReturnHelper The current ReturnHelper instance.
		 */
		public function setResult($result) {
			$this->results[] = $result;

			return $this;
		}

		/**
		 * Adds multiple results to the instance.
		 * 
		 * @param array $results Array of results to add.
		 * @return \N2f\ReturnHelper The current ReturnHelper instance.
		 */
		public function setResults(array $results) {
			if (count($results) < 1) {
				return $this;
			}

			foreach (array_values($results) as $Res) {
				$this->results[] = $Res;
			}

			return $this;
		}

		/**
		 * Sets the instance status to BAD.
		 * 
		 * @return \N2f\ReturnHelper The current ReturnHelper instance.
		 */
		public function setBad() {
			$this->status = self::BAD;

			return $this;
		}

		/**
		 * Alias of SetGud() for those with stricter
		 * english requirements.
		 * 
		 * @return \N2f\ReturnHelper The current ReturnHelper instance.
		 */
		public function setGood() {
			return $this->SetGud();
		}

		/**
		 * Sets the instance status to GUD.
		 * 
		 * @return \N2f\ReturnHelper The current ReturnHelper instance.
		 */
		public function setGud() {
			$this->status = self::GUD;

			return $this;
		}

		/**
		 * Sets the instance status.
		 * 
		 * @param int $status Integer value of new instance status.
		 * @return \N2f\ReturnHelper The current ReturnHelper instance.
		 */
		public function setStatus($status) {
			$this->status = intval($status);

			return $this;
		}
	}
