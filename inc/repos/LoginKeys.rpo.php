<?php

	/**
	 * Repository class for common login key actions.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class LoginKeys extends BaseRepo {
		/**
		 * Retrieves any and all login keys associated
		 * with the provided user identifier.
		 * 
		 * @param integer $userId Integer value of user identifier.
		 * @return \LoginKey[] Returns any found login keys for user, empty array on error.
		 */
		public function getAllForUser($userId) {
			if ($userId < 1) {
				$this->log->error("Error retrieving login keys: GetAllForUser called with invalid user identifier");

				return array();
			}

			$ret = array();

			try {
				$stmt = $this->db->prepare("SELECT `UserID`, `Provider`, `Key` FROM `LoginKey` WHERE `UserID` = :userid");
				$stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$ret[] = LoginKey::fromArray($this->db, $row);
					}
				}
			} catch (PDOException $ex) {
				$this->log->error("Error retrieving login keys: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}

		/**
		 * Retrieves a single login key given the user identifier
		 * and provider.
		 * 
		 * @param integer $userId Integer value of user identifier.
		 * @param integer $provider Integer value of provider.
		 * @return null|\LoginKey Returns a LoginKey object if found, null otherwise.
		 */
		public function getForUserAndProvider($userId, $provider) {
			if ($userId < 1) {
				$this->log->error("Error retrieving login key: GetForUserAndProvider called with invalid user identifier");

				return null;
			}

			if ($provider < 1) {
				$this->log->error("Error retrieving login key: GetForUserAndProvider called without non-zero provider");

				return null;
			}

			$ret = null;

			try {
				$stmt = $this->db->prepare("SELECT `UserID`, `Provider`, `Key` FROM `LoginKey` WHERE `UserID` = :userid AND `Provider` = :provider");
				$stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
				$stmt->bindParam(':provider', $provider, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$ret = LoginKey::fromArray($this->db, $stmt->fetch(PDO::FETCH_ASSOC));
				}
			} catch (PDOException $ex) {
				$this->log->error("Error retrieving login key: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}
	}
