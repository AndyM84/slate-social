<?php

	/**
	 * Repository class for common user role actions.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class UserRoles extends BaseRepo {
		/**
		 * Determines whether or not user is in role.
		 *
		 * @param integer $userId User identifier to search for in database.
		 * @param integer $roleId Role identifier to search for in database.
		 * @return boolean Returns true if user in role, false on error.
		 */
		public function userInRoleByRoleId($userId, $roleId) {
			if ($userId < 1) {
				$this->log->error("Error checking user role: UserInRoleByRoleId called without non-zero user identifier");

				return false;
			}

			if ($roleId < 1) {
				$this->log->error("Error checking user role: UserInRoleByRoleId called without non-zero role identifier");

				return false;
			}

			try {
				$stmt = $this->db->prepare("SELECT 1 FROM `UserRole` WHERE `UserID` = :userid AND `RoleID` = :roleid");

				$stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
				$stmt->bindParam(':roleid', $roleId, PDO::PARAM_INT);

				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					return true;
				}
			} catch (PDOException $ex) {
				$this->log->error("Error checking user role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return false;
		}

		/**
		 * Determines whether or not user is in role.
		 *
		 * @param integer $userId User identifier to search for in databse.
		 * @param string $name Role name to search for in database.
		 * @return boolean Returns true if user in role, false on error.
		 */
		public function userInRoleByRoleName($userId, $name) {
			if ($userId < 1) {
				$this->log->error("Error checking user role: UserInRoleByRoleName called without non-zero user identifier");

				return false;
			}

			if (empty($name)) {
				$this->log->error("Error checking user role: UserInRoleByRoleName called without valid role name");

				return false;
			}

			try {
				$stmt = $this->db->prepare("SELECT `ID` FROM `Role` WHERE `Name` = :name");
				$stmt->bindParam(':name', $name, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->log->error("Error checking user role: Role not found with provided name");

					return false;
				}

				$res = $stmt->fetch();
				$stmt = $this->db->prepare("SELECT 1 FROM `UserRole` WHERE `UserID` = :userid AND `RoleID` = :roleid");

				$stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
				$stmt->bindParam(':roleid', $res['ID'], PDO::PARAM_INT);

				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					return true;
				}
			} catch (PDOException $ex) {
				$this->log->error("Error checking user role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return false;
		}

		/**
		 * Adds an existing user to an existing role.
		 *
		 * @param integer $userId User identifier to add in database.
		 * @param integer $roleId Role identifier to add in database.
		 */
		public function addUserToRoleByRoleId($userId, $roleId) {
			if ($userId < 1) {
				$this->log->error("Error adding user role: AddUserToRoleByRoleId called without non-zero user identifier");

				return;
			}

			if ($roleId < 1) {
				$this->log->error("Error adding user role: AddUserToRoleByRoleId called without non-zero role identifier");

				return;
			}

			try {
				$stmt = $this->db->prepare("SELECT `ID` FROM `Role` WHERE `ID` = :id");
				$stmt->bindParam(':id', $roleId, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->log->error("Error adding user role: Role not found with provided identifier");

					return;
				}

				$stmt = $this->db->prepare("SELECT `ID` FROM `User` WHERE `ID` = :id");
				$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->log->error("Error adding user role: User not found with provided identifier");

					return;
				}

				$stmt = $this->db->prepare("INSERT INTO `UserRole` (`UserID`, `RoleID`) VALUES (:userid, :roleid)");

				$stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
				$stmt->bindParam(':roleid', $roleId, PDO::PARAM_INT);

				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error adding user role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return;
		}

		/**
		 * Adds an existing user to an existing role by role name.
		 *
		 * @param integer $userId User identifier to add in database.
		 * @param string $name Role name to add in database.
		 */
		public function addUserToRoleByRoleName($userId, $name) {
			if ($userId < 1) {
				$this->log->error("Error adding user role: AddUserToRoleByRoleName called without non-zero user identifier");

				return;
			}

			if (empty($name)) {
				$this->log->error("Error adding user role: AddUserToRoleByRoleName called with invalid role name");

				return;
			}

			try {
				$stmt = $this->db->prepare("SELECT `ID` FROM `Role` WHERE `Name` = :name");
				$stmt->bindParam(':name', $name, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->log->error("Error adding user role: Role not found with provided identifier");

					return;
				}

				$res = $stmt->fetch();

				$stmt = $this->db->prepare("SELECT `ID` FROM `User` WHERE `ID` = :id");
				$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->log->error("Error adding user role: User not found with provided identifier");

					return;
				}

				$stmt = $this->db->prepare("INSERT INTO `UserRole` (`UserID`, `RoleID`) VALUES (:userid, :roleid)");

				$stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
				$stmt->bindParam(':roleid', $res['ID'], PDO::PARAM_INT);

				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error adding user role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return;
		}

		/**
		 * Removes a user from a role by role identifier.
		 * 
		 * @param integer $userId User identifier to remove in database.
		 * @param integer $roleId Role identifier to remove in database.
		 */
		public function removeUserFromRoleByRoleId($userId, $roleId) {
			if ($userId < 1) {
				$this->log->error("Error deleting user role: AddUserToRoleByRoleId called without non-zero user identifier");

				return;
			}

			if ($roleId < 1) {
				$this->log->error("Error deleting user role: AddUserToRoleByRoleId called without non-zero role identifier");

				return;
			}

			try {
				$stmt = $this->db->prepare("SELECT `ID` FROM `Role` WHERE `ID` = :id");
				$stmt->bindParam(':id', $roleId, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->log->error("Error deleting user role: Role not found with provided identifier");

					return;
				}

				$stmt = $this->db->prepare("SELECT `ID` FROM `User` WHERE `ID` = :id");
				$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->log->error("Error deleting user role: User not found with provided identifier");

					return;
				}

				$stmt = $this->db->prepare("DELETE FROM `UserRole` WHERE `UserID` = :userid AND `RoleID` = :roleid LIMIT 1");

				$stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
				$stmt->bindParam(':roleid', $roleId, PDO::PARAM_INT);

				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error deleting user role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return;
		}

		/**
		 * Removes a user from a role by role name.
		 * 
		 * @param integer $userId User identifier to remove in database.
		 * @param string $name Role name to remove in database.
		 */
		public function removeUserFromRoleByRoleName($userId, $name) {
			if ($userId < 1) {
				$this->log->error("Error deleting user role: AddUserToRoleByRoleName called without non-zero user identifier");

				return;
			}

			if (empty($name)) {
				$this->log->error("Error deleting user role: AddUserToRoleByRoleName called with invalid role name");

				return;
			}

			try {
				$stmt = $this->db->prepare("SELECT `ID` FROM `Role` WHERE `Name` = :name");
				$stmt->bindParam(':name', $name, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->log->error("Error deleting user role: Role not found with provided identifier");

					return;
				}

				$res = $stmt->fetch();

				$stmt = $this->db->prepare("SELECT `ID` FROM `User` WHERE `ID` = :id");
				$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->log->error("Error deleting user role: User not found with provided identifier");

					return;
				}

				$stmt = $this->db->prepare("DELETE FROM `UserRole` WHERE `UserID` = :userid AND `RoleID` = :roleid LIMIT 1");

				$stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
				$stmt->bindParam(':roleid', $res['ID'], PDO::PARAM_INT);

				$stmt->execute();
			}
			catch (PDOException $ex) {
				$this->log->error("Error deleting user role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return;
		}
	}
