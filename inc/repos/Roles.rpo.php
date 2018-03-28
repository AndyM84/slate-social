<?php

	/**
	 * Repository class for common role actions.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class Roles extends BaseRepo {
		/**
		 * Retrieves a single role by name.
		 * 
		 * @param string $name String value to use when searching database.
		 * @return null|\Role Returns a Role object if found, null otherwise.
		 */
		public function getByName($name) {
			if (empty($name)) {
				$this->log->error("Error retrieving role: GetByName called with invalid name");

				return null;
			}

			$ret = null;

			try {
				$stmt = $this->db->prepare("SELECT `ID`, `Name` FROM `Role` WHERE `Name` = :name");
				$stmt->bindParam(':name', $name, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$ret = Role::fromArray($this->db, $stmt->fetch(PDO::FETCH_ASSOC));
				}
			} catch (PDOException $ex) {
				$this->log->error("Error retrieving role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}

		/**
		 * Retrieve all roles in database.
		 * 
		 * @return \Role[] Returns an array of Role objects, empty array on error.
		 */
		public function getAll() {
			$ret = array();

			try {
				$stmt = $this->db->prepare("SELECT `ID`, `Name` FROM `Role`");
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$ret[] = Role::fromArray($this->db, $row);
					}
				}
			} catch (PDOException $ex) {
				$this->log->error("Error retrieving roles: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}

		/**
		 * Either retrieves a role identifier or creates
		 * the role and then returns the new identifier.
		 *
		 * @param string $name String value to check/create in role table.
		 * @return integer
		 */
		public function createIfMissing($name) {
			$ret = 0;

			try {
				$stmt = $this->db->prepare("SELECT `ID` FROM `Role` WHERE `Name` = :name");
				$stmt->bindParam(':name', $name, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$row = $stmt->fetch();
					$ret = $row['ID'];
				} else {
					$stmt = $this->db->prepare("INSERT INTO `Role` (`Name`) VALUES (:name)");
					$stmt->bindParam(':name', $name, PDO::PARAM_STR);
					$stmt->execute();

					$ret = intval($this->db->lastInsertId());
				}
			} catch (PDOException $ex) {
				$this->log->error("Error finding/creating role '{NAME}': {MESSAGE}", array('NAME' => $name, 'MESSAGE' => $ex->getMessage()));
			}

			return $ret;
		}
	}
