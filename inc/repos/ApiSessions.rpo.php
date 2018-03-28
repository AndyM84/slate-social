<?php

	/**
	 * Repository class for common API session actions.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class ApiSessions extends BaseRepo {
		/**
		 * Retrieves a single API session using the
		 * token identifier.
		 * 
		 * @param string $token String value of token identifier.
		 * @return null|\ApiSession Returns an ApiSession object if found, null otherwise.
		 */
		public function getByToken($token) {
			if (empty($token)) {
				$this->log->error("Error retrieving API session: GetByToken called with invalid token");

				return null;
			}

			$ret = null;

			try {
				$stmt = $this->db->prepare("SELECT `ID`, `UserID`, `Token`, `Hostname`, `Address`, `Created` FROM `ApiSession` WHERE `Token` = :token");
				$stmt->bindParam(':token', $token, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$ret = ApiSession::fromArray($this->db, $stmt->fetch(PDO::FETCH_ASSOC));
				}
			} catch (PDOException $ex) {
				$this->log->error("Error retrieving API session: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}
	}
