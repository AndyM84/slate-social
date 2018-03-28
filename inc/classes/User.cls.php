<?php

	/**
	 * Class that represents a system user.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class User extends BaseClass {
		/**
		 * Unique identifier for user.
		 * 
		 * @var int
		 */
		public $id;
		/**
		 * Username value.
		 * 
		 * @var string
		 */
		public $username;
		/**
		 * User's email address.
		 * 
		 * @var string
		 */
		public $email;
		/**
		 * Whether or not user's email is confirmed.
		 * 
		 * @var bool
		 */
		public $emailConfirmed;
		/**
		 * Date the user joined the site.
		 * 
		 * @var DateTime
		 */
		public $dateJoined = null;
		/**
		 * Optional date the user last logged into the site.
		 * 
		 * @var DateTime
		 */
		public $lastLogin = null;


		/**
		 * Overloads array-based instantiation for User objects.
		 * 
		 * @param PDO $db PDO connection resource for use by object.
		 * @param array $source Array to attempt using as object property values.
		 * @return \User
		 */
		public static function fromArray(PDO $db, array $source) {
			$ret = parent::fromArray($db, $source);
			$ret->dateJoined = ($ret->dateJoined !== null && !empty($ret->dateJoined)) ? new DateTime($ret->dateJoined, new DateTimeZone('UTC')) : null;
			$ret->lastLogin = ($ret->lastLogin !== null && !empty($ret->lastLogin)) ? new DateTime($ret->lastLogin, new DateTimeZone('UTC')) : null;
			$ret->emailConfirmed = ($ret->emailConfirmed == 1) ? true : false;

			return $ret;
		}

		/**
		 * Allows the shallow or deep loading of a User object.
		 * 
		 * @param PDO $db PDO connection resource for use by object.
		 * @param N2f\ParameterHelper $parameters Parameter set to use for loading object.
		 * @param boolean $onlyUseId Boolean value to allow shallow loading (non-DB-based loading) of object.
		 * @param N2f\Logger $log Optional logger injection, new Logger instance created if not supplied.
		 * @return \User
		 */
		public static function fromParameterHelper(PDO $db, N2f\ParameterHelper $parameters, $onlyUseId = true, N2f\Logger $log = null) {
			$tmp = new User($db, null, $log);

			if ($onlyUseId) {
				if (!$parameters->hasValue('id')) {
					return null;
				}

				$tmp->id = $parameters->getInt('id');
				$tmp->read();
			} else {
				$props = array(
					'id',
					'username',
					'email',
					'emailConfirmed',
					'dateJoined',
					'lastLogin'
				);

				foreach (array_values($props) as $prop) {
					if ($parameters->hasValue($prop)) {
						$tmp->{$prop} = $parameters->getString($prop);
					}
				}

				if ($tmp->dateJoined !== null && !empty($tmp->dateJoined)) {
					$tmp->dateJoined = new DateTime($tmp->dateJoined, new DateTimeZone('UTC'));
				}

				if ($tmp->lastLogin !== null && !empty($tmp->lastLogin)) {
					$tmp->lastLogin = new DateTime($tmp->lastLogin, new DateTimeZone('UTC'));
				}
			}

			return $tmp;
		}

		/**
		 * Instantiates a new User object with optional auto-load
		 * via $id parameter. Also includes optional logger injection.
		 * 
		 * @param PDO $db PDO connection resource for use by object.
		 * @param integer $id Optional integer value for User id, triggers auto-load via User::read().
		 * @param N2f\Logger $log Optional logger injection, new Logger instance created if not supplied.
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
		 * Attempts to create (insert) a User into the database.
		 * If optional $canDupe parameter is set, will toggle
		 * checking for duplicate users in database.
		 * 
		 * @param boolean $canDupe Optional boolean toggle for duplicate check, based off of email address if used.
		 * @throws Exception Thrown if non-zero identifier used, duplicate check finds an existing user, or there was an error on the insert operation.
		 */
		public function create($canDupe = false) {
			if ($this->id > 0) {
				$this->log->error("Error creating user: Create called with non-zero user identifier");

				throw new Exception("Cannot create a user with a non-zero identifier");
			}

			try {
				if ($canDupe === false) {
					$stmt = $this->db->prepare("SELECT `ID` FROM `User` WHERE `Email` = :email");
					$stmt->bindParam(":email", $this->email, PDO::PARAM_STR);
					$stmt->execute();

					if ($stmt->rowCount() > 0) {
						$this->log->error("Error creating user: Duplicate user found while enforcing unique entries");

						throw new Exception("Cannot create a user with a duplicate email address");
					}
				}

				if ($this->dateJoined === null) {
					$this->dateJoined = new DateTime('now', new DateTimeZone('UTC'));
				}

				$emailConfirmed = ($this->emailConfirmed == false) ? 0 : 1;
				$dateJoined = $this->dateJoined->format("Y-m-d G:i:s");
				$lastLogin = null;

				$stmt = $this->db->prepare("INSERT INTO `User` (`Username`, `Email`, `EmailConfirmed`, `DateJoined`, `LastLogin`) VALUES (:username, :email, :emailconfirmed, :datejoined, :lastlogin)");

				$stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
				$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
				$stmt->bindParam(':emailconfirmed', $emailConfirmed, PDO::PARAM_INT);
				$stmt->bindParam(':datejoined', $dateJoined, PDO::PARAM_STR);
				$stmt->bindParam(':lastlogin', $lastLogin, PDO::PARAM_NULL);

				$stmt->execute();
				$this->id = intval($this->db->lastInsertId());
			} catch (PDOException $ex) {
				$this->log->error("Error creating user: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to read User info from the database.
		 * 
		 * @throws Exception Thrown if called with 0 as identifier or if there was an error on the select operation.
		 */
		public function read() {
			if ($this->id < 1) {
				$this->log->error("Error reading user: Read called without non-zero user identifier");

				throw new Exception("Cannot read a user without an identifier");
			}

			try {
				$stmt = $this->db->prepare("SELECT `Username`, `Email`, `EmailConfirmed`, `DateJoined`, `LastLogin` FROM `User` WHERE `ID` = :id");
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() != 1) {
					$this->id = 0;
				} else {
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$this->username = $row['Username'];
					$this->email = $row['Email'];
					$this->emailConfirmed = ($row['EmailConfirmed'] == 0) ? false : true;
					$this->dateJoined = new DateTime($row['DateJoined'], new DateTimeZone('UTC'));
					$this->lastLogin = (empty($row['LastLogin'])) ? null : new DateTime($row['LastLogin'], new DateTimeZone('UTC'));
				}
			} catch (PDOException $ex) {
				$this->log->error("Error reading user: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to update User info to the database.
		 * 
		 * @throws Exception Thrown if called with 0 as identifier or if there was an error on the update operation.
		 */
		public function update() {
			if ($this->id < 1) {
				$this->log->error("Error updating user: Update called without non-zero user identifier");

				throw new Exception("Cannot update a user without an identifier");
			}

			try {
				$stmt = $this->db->prepare("UPDATE `User` SET `Username` = :username, `Email` = :email, `EmailConfirmed` = :emailconfirmed, `DateJoined` = :datejoined, `LastLogin` = :lastlogin WHERE `ID` = :id");

				$emailConfirmed = ($this->emailConfirmed == false) ? 0 : 1;
				$dateJoined = $this->dateJoined->format("Y-m-d G:i:s");
				$lastLogin = ($this->lastLogin === null) ? null : $this->lastLogin->format("Y-m-d G:i:s");

				$stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
				$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
				$stmt->bindParam(':emailconfirmed', $emailConfirmed, PDO::PARAM_INT);
				$stmt->bindParam(':datejoined', $dateJoined, PDO::PARAM_STR);
				$stmt->bindParam(':lastlogin', $lastLogin, ($lastLogin === null) ? PDO::PARAM_NULL : PDO::PARAM_STR);
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error updating user: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to delete a User from the database.
		 * 
		 * @throws Exception Thrown if called with 0 as identifier or if there was an error on the delete operation.
		 */
		public function delete() {
			if ($this->id < 1) {
				$this->log->error("Error deleting user: Delete called without non-zero user identifier");

				throw new Exception("Cannot delete a user without an identifier");
			}

			try {
				$stmt = $this->db->prepare("DELETE FROM `User` WHERE `ID` = :id LIMIT 1");
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error deleting user: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}
	}
