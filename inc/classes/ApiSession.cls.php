<?php

	/**
	 * Class that represents authenticated sessions
	 * for API requests.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class ApiSession extends BaseClass {
		/**
		 * Unique identifier for session.
		 * 
		 * @var integer
		 */
		public $id;
		/**
		 * Associated user identifier.
		 * 
		 * @var integer
		 */
		public $userId;
		/**
		 * Token to minorly obfuscate session
		 * identification.
		 * 
		 * @var string
		 */
		public $token;
		/**
		 * Hostname that originated session.
		 * 
		 * @var string
		 */
		public $hostname;
		/**
		 * IP address that originated session.
		 * 
		 * @var string
		 */
		public $address;
		/**
		 * DateTime the session was created.
		 * 
		 * @var \DateTime
		 */
		public $created = null;

		
		/**
		 * Overloads array-based instantiation for ApiSession
		 * objects.
		 * 
		 * @param PDO $db PDO connection resource for use by object.
		 * @param array $source Array to attempt using as object property values.
		 * @return \ApiSession
		 */
		public static function fromArray(PDO $db, array $source) {
			$ret = parent::fromArray($db, $source);
			$ret->created = new DateTime($source['Created'], new DateTimeZone('UTC'));

			return $ret;
		}

		/**
		 * Instantiates a new ApiSession object with optional
		 * auto-load via $id parameter. Also includes optional
		 * Logger injection.
		 * 
		 * @param PDO $db PDO connection resource for use by object.
		 * @param mixed $id Optional integer value for ApiSession identifier, triggers auto-load via ApiSession::read().
		 * @param N2f\Logger $log Optional Logger injection, new Logger instance created if not supplied.
		 */
		public function __construct(PDO $db, $id = null, N2f\Logger $log = null) {
			parent::__construct($db, $log);

			if ($id !== null) {
				$this->id = intval($id);
				$this->read();
			}

			return;
		}

		/**
		 * Attempts to create (insert) an ApiSession into
		 * the database.
		 * 
		 * @throws Exception Thrown if non-zero identifier used, no user identifier supplied, or there is an error in the insert operation.
		 */
		public function create() {
			if ($this->id > 0) {
				$this->log->error("Error creating API session: Create called with non-zero identifier");

				throw new Exception("Cannot create an API session with an identifier");
			}

			if ($this->userId < 1) {
				$this->log->error("Error creating API session: Create called without non-zero user identifier");

				throw new Exception("Cannot create API session without a user identifier");
			}

			try {
				if ($this->created === null) {
					$this->created = new DateTime('now', new DateTimeZone('UTC'));
				}

				$created = $this->created->format('Y-m-d G:i:s');
				$stmt = $this->db->prepare("INSERT INTO `ApiSession` (`UserID`, `Token`, `Hostname`, `Address`, `Created`) VALUES (:userid, :token, :hostname, :address, :created)");

				$stmt->bindParam(':userid', $this->userId, PDO::PARAM_INT);
				$stmt->bindParam(':token', $this->token, PDO::PARAM_STR);
				$stmt->bindParam(':hostname', $this->hostname, PDO::PARAM_STR);
				$stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
				$stmt->bindParam(':created', $created, PDO::PARAM_STR);

				$stmt->execute();
				$this->id = intval($this->db->lastInsertId());
			} catch (PDOException $ex) {
				$this->log->error("Error creating API session: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to read (select) an ApiSession from
		 * the database.
		 * 
		 * @throws Exception Thrown if called without identifier or there is an error with the select operation.
		 */
		public function read() {
			if ($this->id < 1) {
				$this->log->error("Error reading API session: Read called without non-zero identifier");

				throw new Exception("Cannot read an API session without an identifier");
			}

			try {
				$stmt = $this->db->prepare("SELECT `UserID`, `Token`, `Hostname`, `Address`, `Created` FROM `ApiSession` WHERE `ID` = :id");
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->id = 0;
				} else {
					$row = $stmt->fetch();
					$this->userId = $row['UserID'];
					$this->token = $row['Token'];
					$this->hostname = $row['Hostname'];
					$this->address = $row['Address'];
					$this->created = new DateTime($row['Created'], new DateTimeZone('UTC'));
				}
			} catch (PDOException $ex) {
				$this->log->error("Error reading API session: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to update an ApiSession in the
		 * database.
		 * 
		 * @throws Exception Thrown if called without an identifier or if there is an error with the update operation.
		 */
		public function update() {
			if ($this->id < 1) {
				$this->log->error("Error updating API session: Update called without non-zero identifier");

				throw new Exception("Cannot update an API session without an identifier");
			}

			try {
				$created = $this->created->format('Y-m-d G:i:s');
				$stmt = $this->db->prepare("UPDATE `ApiSession` SET `UserID` = :userid, `Token` = :token, `Hostname` = :hostname, `Address` = :address, `Created` = :created WHERE `ID` = :id");

				$stmt->bindParam(':userid', $this->userId, PDO::PARAM_INT);
				$stmt->bindParam(':token', $this->token, PDO::PARAM_STR);
				$stmt->bindParam(':hostname', $this->hostname, PDO::PARAM_STR);
				$stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
				$stmt->bindParam(':created', $created, PDO::PARAM_STR);
				$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);

				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error updating API session: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to delete an ApiSession from the
		 * database.
		 * 
		 * @throws Exception Thrown if called without an identifier or if there is an error with the delete operation.
		 */
		public function delete() {
			if ($this->id < 1) {
				$this->log->error("Error deleting API session: Delete called without non-zero identifier");

				throw new Exception("Cannot delete an API session without an identifier");
			}

			try {
				$stmt = $this->db->prepare("DELETE FROM `ApiSession` WHERE `ID` = :id LIMIT 1");
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error deleting API session: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}
	}
