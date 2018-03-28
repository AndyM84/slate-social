<?php

	/**
	 * Class that represents login keys for use
	 * with authentication providers.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class LoginKey extends BaseClass {
		const PROVIDER_ERROR = 0;
		const PROVIDER_BASIC = 1;
		const PROVIDER_FACEBOOK = 2;
		const PROVIDER_TWITTER = 3;

		/**
		 * Array lookup of valid providers, used
		 * by LoginKey::isValidProvider().
		 * 
		 * @var array
		 */
		private $validProviderLookup = array(
			self::PROVIDER_BASIC => true,
			self::PROVIDER_FACEBOOK => true,
			self::PROVIDER_TWITTER => true
		);

		/**
		 * UserID associated with this login key.
		 * 
		 * @var integer
		 */
		public $userId;
		/**
		 * Provider type for this login key.
		 * 
		 * @var integer
		 */
		public $provider;
		/**
		 * Value of login key.
		 * 
		 * @var string
		 */
		public $key;


		/**
		 * Instantiates a new LoginKey object.  If $userId and $provider
		 * are supplied and valid, object will attempt a call to
		 * LoginKey::update() automatically.
		 * 
		 * @param PDO $db PDO resource object to use for queries.
		 * @param integer $userId Optional integer value for user identifer.
		 * @param integer $provider Optional integer value for provider.
		 * @param N2f\Logger $log Optional N2f\Logger instance.
		 */
		public function __construct(PDO $db, $userId = null, $provider = null, N2f\Logger $log = null) {
			parent::__construct($db, $log);
			$this->userId = ($userId !== null) ? intval($userId) : 0;
			$this->provider = ($provider !== null) ? intval($provider) : 0;

			if ($this->userId > 0 && $this->isValidProvider()) {
				$this->read();
			}

			return;
		}

		/**
		 * Attempts to create (insert) a LoginKey object
		 * into the database.
		 * 
		 * @throws Exception Thrown if called 0 as user identifier, invalid provider, duplicate key found, or if there was an error in the insert operation.
		 */
		public function create() {
			if ($this->userId < 1) {
				$this->log->error("Error creating login key: Create called without non-zero user identifier");

				throw new Exception("Cannot create a login key without non-zero user identifier");
			}

			if (!$this->isValidProvider()) {
				$this->log->error("Error creating login key: Create called without valid provider");

				throw new Exception("Cannot create a login key without a valid provider");
			}

			try {
				$stmt = $this->db->prepare("SELECT `UserID` FROM `LoginKey` WHERE `UserID` = :userid AND `Provider` = :provider");
				$stmt->bindParam(':userid', $this->userId, PDO::PARAM_INT);
				$stmt->bindParam(':provider', $this->provider, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$this->log->error("Error creating login key: Create called for duplicate user/provider pair");

					throw new Exception("Cannot create a duplicate login key");
				}

				$stmt = $this->db->prepare("INSERT INTO `LoginKey` (`UserID`, `Provider`, `Key`) VALUES (:userid, :provider, :key)");
				$stmt->bindParam(':userid', $this->userId, PDO::PARAM_INT);
				$stmt->bindParam(':provider', $this->provider, PDO::PARAM_INT);
				$stmt->bindParam(':key', $this->key, PDO::PARAM_STR);
				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error('Error creating login key: {MESSAGE}', array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to read (select) a LoginKey object
		 * based on the user identifier and provider
		 * type.
		 * 
		 * @throws Exception Thrown if called with 0 as user identifier, invalid provider, or if there was an error in the select operation.
		 */
		public function read() {
			if ($this->userId < 1) {
				$this->log->error("Error reading login key: Read called without non-zero user identifier");

				throw new Exception("Cannot read a login key without a user identifier");
			}

			if (!$this->isValidProvider()) {
				$this->log->error("Error reading login key: Read called without a valid provider");

				throw new Exception("Cannot read login key without a valid provider");
			}

			try {
				$stmt = $this->db->prepare("SELECT `UserID`, `Provider`, `Key` FROM `LoginKey` WHERE `UserID` = :userid AND `Provider` = :provider");
				$stmt->bindParam(':userid', $this->userId, PDO::PARAM_INT);
				$stmt->bindParam(':provider', $this->provider, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->log->warning("Warning reading login key: No key found for user/provider pair, may not exist");

					$this->userId = 0;
					$this->provider = self::PROVIDER_ERROR;
				} else {
					$row = $stmt->fetch();
					$this->key = $row['Key'];
				}
			} catch (PDOException $ex) {
				$this->log->error("Error reading login key: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to update a LoginKey in the
		 * database.
		 * 
		 * @throws Exception Thrown if called with 0 as user identifier, invalid provider, or if there was an error in the update operation.
		 */
		public function update() {
			if ($this->userId < 1) {
				$this->log->error("Error updating login key: Update called without non-zero user identifier");

				throw new Exception("Cannot update a login key without a user identifier");
			}

			if (!$this->isValidProvider()) {
				$this->log->error("Error updating login key: Update called without a valid provider");

				throw new Exception("Cannot update a login key without a valid provider");
			}

			try {
				$stmt = $this->db->prepare("UPDATE `LoginKey` SET `Key` = :key WHERE `UserID` = :userid AND `Provider` = :provider");
				
				$stmt->bindParam(':key', $this->key, PDO::PARAM_STR);
				$stmt->bindParam(':userid', $this->userId, PDO::PARAM_INT);
				$stmt->bindParam(':provider', $this->provider, PDO::PARAM_INT);
				
				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error updating login key: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to delete a LoginKey in the
		 * database.
		 * 
		 * @throws Exception Thrown if called with 0 as user identifier, invalid provider, or if there was an error in the delete operation.
		 */
		public function delete() {
			if ($this->userId < 1) {
				$this->log->error("Error deleting login key: Delete called without non-zero user identifier");

				throw new Exception("Cannot delete a login key without a user identifier");
			}

			if (!$this->isValidProvider()) {
				$this->log->error("Error deleting login key: Delete called without a valid provider");

				throw new Exception("Cannot delete a login key without a valid provider");
			}

			try {
				$stmt = $this->db->prepare("DELETE FROM `LoginKey` WHERE `UserID` = :userid AND `Provider` = :provider LIMIT 1");
				$stmt->bindParam(':userid', $this->userId, PDO::PARAM_INT);
				$stmt->bindParam(':provider', $this->provider, PDO::PARAM_INT);
				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error deleting login key: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Compares the object's currently set provider
		 * against the set of valid providers.
		 * 
		 * @return boolean
		 */
		protected function isValidProvider() {
			if (array_key_exists($this->provider, $this->validProviderLookup)) {
				return true;
			}

			return false;
		}
	}
