<?php

	/**
	 * Repository class for common user actions.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class Users extends BaseRepo {
		/**
		 * Retrieves a single user from the database using
		 * the email address as an identifier.  If provided,
		 * $expectedCount will only allow a returned user if
		 * the number found matches the expected count.
		 * 
		 * @param string $emailAddress Value to query against as the email address.
		 * @param integer $expectedCount Optional number of users expected with email address.
		 * @throws InvalidArgumentException If email address is null or empty, search is not performed.
		 * @return null|\User Returns a User object if found, null otherwise.
		 */
		public function getByEmail($emailAddress, $expectedCount = null) {
			if (empty($emailAddress)) {
				$this->log->error("Error retrieving user: GetByEmail called with invalid email address");

				return null;
			}

			$ret = null;

			try {
				$stmt = $this->db->prepare("SELECT `ID`, `Username`, `Email`, `EmailConfirmed`, `DateJoined`, `LastLogin` FROM `User` WHERE `Email` = :email");
				$stmt->bindParam(':email', $emailAddress, PDO::PARAM_STR);
				$stmt->execute();

				if (($expectedCount !== null && $stmt->rowCount() == $expectedCount) || $stmt->rowCount() > 0) {
					$ret = User::fromArray($this->db, $stmt->fetch(PDO::FETCH_ASSOC));
				}
			} catch (PDOException $ex) {
				$this->log->error("Error retrieving user: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}

		/**
		 * Retrieves a single user from the database using
		 * the username as an identifier.  If provided,
		 * $expectedCount will only allow a returned user if
		 * the number found matches the expected count.
		 * 
		 * @param string $username Value to query against as the username.
		 * @param integer $expectedCount Optional number of users expected with username.
		 * @throws InvalidArgumentException If username is null or empty, search is not performed.
		 * @return null|\User Returns a User object if found, null otherwise.
		 */
		public function getByUsername($username, $expectedCount = null) {
			if (empty($username)) {
				$this->log->error("Error retrieving user: GetByUsername was called with invalid username");

				return null;
			}

			$ret = null;

			try {
				$stmt = $this->db->prepare("SELECT `ID`, `Username`, `Email`, `EmailConfirmed`, `DateJoined`, `LastLogin` FROM `User` WHERE `Username` = :username");
				$stmt->bindParam(':username', $username, PDO::PARAM_STR);
				$stmt->execute();

				if (($expectedCount !== null && $stmt->rowCount() == $expectedCount) || $stmt->rowCount() > 0) {
					$ret = User::fromArray($this->db, $stmt->fetch(PDO::FETCH_ASSOC));
				}
			} catch (PDOException $ex) {
				$this->log->error("Error retrieving user: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}

		/**
		 * Retrieves a single user from the database using
		 * the username or email address as an identifier.  If
		 * provided, $expectedCount will only allow a returned
		 * user if the number found matches the expected count.
		 * 
		 * @param string $value Value to query against as identifier.
		 * @param integer $expectedCount Optional number of users expected with identifier.
		 * @throws InvalidArgumentException If value is null or empty, search is not performed.
		 * @return null|\User Returns a User object if found, null otherwise.
		 */
		public function getByUsernameOrEmail($value, $expectedCount = null) {
			if (empty($value)) {
				$this->log->error("Error retrieving user: GetByUsernameOrEmail called with invalid email/username");

				return null;
			}

			$ret = null;

			try {
				$stmt = $this->db->prepare("SELECT `ID`, `Username`, `Email`, `EmailConfirmed`, `DateJoined`, `LastLogin` FROM `User` WHERE `Username` = :username OR `Email` = :email");
				$stmt->bindParam(':username', $value, PDO::PARAM_STR);
				$stmt->bindParam(':email', $value, PDO::PARAM_STR);
				$stmt->execute();

				if (($expectedCount !== null && $stmt->rowCount() == $expectedCount) || $stmt->rowCount() > 0) {
					$ret = User::fromArray($this->db, $stmt->fetch(PDO::FETCH_ASSOC));
				}
			} catch (PDOException $ex) {
				$this->log->error("Error retrieving user: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}

		/**
		 * Checks if a given username string matches the requirements
		 * of a username in the system, optionally also if it is unique
		 * against the database.
		 * 
		 * @param string $username String value to check for username validity.
		 * @param boolean $checkUnique Optionally triggers a unique check against the database.
		 * @return N2f\ReturnHelper A ReturnHelper object with a single error message if 'bad'.
		 */
		public function isValidUsername($username, $checkUnique = false) {
			$minLength = 6;
			$maxLength = 64;
			$acceptableChars = array(
				'a' => true, 'b' => true, 'c' => true, 'd' => true, 'e' => true, 'f' => true, 'g' => true, 'h' => true,
				'i' => true, 'j' => true, 'k' => true, 'l' => true, 'm' => true, 'n' => true, 'o' => true, 'p' => true,
				'q' => true, 'r' => true, 's' => true, 't' => true, 'u' => true, 'v' => true, 'w' => true, 'x' => true,
				'y' => true, 'z' => true, 'A' => true, 'B' => true, 'C' => true, 'D' => true, 'E' => true, 'F' => true,
				'G' => true, 'H' => true, 'I' => true, 'J' => true, 'K' => true, 'L' => true, 'M' => true, 'N' => true,
				'O' => true, 'P' => true, 'Q' => true, 'R' => true, 'S' => true, 'T' => true, 'U' => true, 'V' => true,
				'W' => true, 'X' => true, 'Y' => true, 'Z' => true, '0' => true, '1' => true, '2' => true, '3' => true,
				'4' => true, '5' => true, '6' => true, '7' => true, '8' => true, '9' => true, '-' => true, '_' => true
			);

			$ulen = strlen($username);
			$ret = new N2f\ReturnHelper();

			if (empty($username) || $ulen < $minLength || $ulen > $maxLength) {
				$ret->setBad();
				$ret->setMessage("Empty or wrong length (must be between {$minLength} and {$maxLength} characters long)");

				return $ret;
			}

			for ($i = 0; $i < $ulen; ++$i) {
				if (!array_key_exists($username[$i], $acceptableChars)) {
					$ret->setBad();
					$ret->setMessage("Invalid character (" . $username[$i] . ")");

					return $ret;
				}
			}

			if ($checkUnique === true) {
				try {
					$stmt = $this->db->prepare("SELECT `ID` FROM `User` WHERE `Username` = :username");
					$stmt->bindParam(':username', $username, PDO::PARAM_STR);
					$stmt->execute();

					if ($stmt->rowCount() > 0) {
						$ret->setBad();
						$ret->setMessage("Duplicate username in database");

						return $ret;
					}
				} catch (PDOException $ex) {
					$ret->setBad();
					$ret->setMessage("Unable to check database for duplicates");
					$this->log->error("Error checking username '{USERNAME}': {MESSAGE}", array('USERNAME' => $username, 'MESSAGE' => $ex->getMessage()));

					return $ret;
				}
			}

			$ret->setGood();

			return $ret;
		}

		/**
		 * Checks if a given email address string matches the requirements
		 * of an email in the system, optionally also if it is unique
		 * against the database.
		 * 
		 * @param string $email String value to check for email validity.
		 * @param boolean $checkUnique Optionally triggers a unique check against the database.
		 * @return N2f\ReturnHelper A ReturnHelper object with a single error message if 'bad'.
		 */
		public function isValidEmail($email, $checkUnique = false) {
			$ret = new N2f\ReturnHelper();

			if (empty($email) || stripos($email, "@") === false) {
				$ret->setBad();
				$ret->setMessage("Empty or invalid format");

				return $ret;
			}

			if ($checkUnique === true) {
				try {
					$stmt = $this->db->prepare("SELECT `ID` FROM `User` WHERE `Email` = :email");
					$stmt->bindParam(':email', $email, PDO::PARAM_STR);
					$stmt->execute();

					if ($stmt->rowCount() > 0) {
						$ret->setBad();
						$ret->setMessage("Duplicate email address in database");

						return $ret;
					}
				} catch (PDOException $ex) {
					$ret->setBad();
					$ret->setMessage("Unable to check database for duplicates");
					$this->log->error("Error checking email address '{EMAIL}': {MESSAGE}", array('EMAIL' => $email, 'MESSAGE' => $ex->getMessage()));

					return $ret;
				}
			}

			$ret->setGood();

			return $ret;
		}

		/**
		 * Checks if a given password matches the requirements of a password
		 * in the system.
		 * 
		 * @param string $password String value to check for password validity.
		 * @return N2f\ReturnHelper A ReturnHelper object with a single error message if 'bad'.
		 */
		public function isValidPassword($password) {
			$minLength = 8;
			$maxLength = 72;
			$ret = new N2f\ReturnHelper();

			$plen = strlen($password);

			if (empty($password) || $plen < $minLength || $plen > $maxLength) {
				$ret->setBad();
				$ret->setMessage("Empty or wrong length (must be between {$minLength} and {$maxLength} characters long)");

				return $ret;
			}

			$ret->setGood();

			return $ret;
		}

		/**
		 * Retrieves all users ordered by ID for raw
		 * data display.
		 * 
		 * @return User[]
		 */
		public function getAllUsers() {
			$ret = array();

			try {
				$stmt = $this->db->prepare("SELECT `ID`, `Username`, `Email`, `EmailConfirmed`, `DateJoined`, `LastLogin` FROM `User` ORDER BY `ID`");
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$ret[] = User::fromArray($this->db, $row);
					}
				}
			} catch (PDOException $ex) {
				$this->log->error("Error retrieving all users: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}

		/**
		 * Retrieves some basic statistics based on
		 * users in the database.
		 * 
		 * @return array
		 */
		public function getUserStatistics() {
			$ret = array(
				BackendStrings::Stats_User_Confirmed => 0,
				BackendStrings::Stats_User_Total => 0,
				BackendStrings::Stats_User_Logins => 0
			);

			try {
				$stmt = $this->db->prepare("SELECT COUNT(*) FROM `User` WHERE `EmailConfirmed` > 0");
				$stmt->execute();
				$row = $stmt->fetch();

				$ret[BackendStrings::Stats_User_Confirmed] = $row['COUNT(*)'];

				$stmt = $this->db->prepare("SELECT COUNT(*) FROM `User`");
				$stmt->execute();
				$row = $stmt->fetch();

				$ret[BackendStrings::Stats_User_Total] = $row['COUNT(*)'];

				$today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
				$stmt = $this->db->prepare("SELECT COUNT(*) FROM `User` WHERE `LastLogin` IS NOT NULL AND `LastLogin` > :lastlogin");
				$stmt->bindParam(':lastlogin', $today, PDO::PARAM_STR);
				$stmt->execute();
				$row = $stmt->fetch();

				$ret[BackendStrings::Stats_User_Logins] = $row['COUNT(*)'];
			} catch (PDOException $ex) {
				$this->log->error("Error retrieving user statistics: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}
	}
