<?php

	/**
	 * Class that represents an access level role.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class Role extends BaseClass {
		/**
		 * Unique identifier for role.
		 * 
		 * @var integer
		 */
		public $id;
		/**
		 * Name for role.
		 * 
		 * @var string
		 */
		public $name;

		
		/**
		 * Instantiates a new Role object, optionally performing auto-load
		 * using a supplied identifier. Also provides an optional Logger
		 * injection parameter.
		 * 
		 * @param PDO $db PDO connection resource to use within object.
		 * @param integer $id Optional integer value to trigger auto-load via Role::read().
		 * @param N2f\Logger $log Optional Logger injection, if not supplied a new Logger instance is created.
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
		 * Attempts to create (insert) a Role into the database.
		 * 
		 * @throws Exception Thrown if called with non-zero identifier, null name, duplicate Role found, or if there was an error in the insert operation.
		 */
		public function create() {
			if ($this->id > 0) {
				$this->log->error("Error creating role: Create called with non-zero identifier");

				throw new Exception("Cannot create role with non-zero identifier");
			}

			if (empty($this->name)) {
				$this->log->error("Error creating role: Create called with empty or null name");

				throw new Exception("Cannot create role without valid name");
			}

			try {
				$stmt = $this->db->prepare("SELECT `ID` FROM `Role` WHERE `Name` = :name");
				$stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					$this->log->error("Error creating role: Create called for duplicate named role");

					throw new Exception("Cannot create role that already exists in database");
				}

				$stmt = $this->db->prepare("INSERT INTO `Role` (`Name`) VALUES (:name)");
				$stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
				$stmt->execute();

				$this->id = intval($this->db->lastInsertId());
			} catch (PDOException $ex) {
				$this->log->error("Error creating role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to read (select) a Role object from the database.
		 * 
		 * @throws Exception Thrown if called with 0 as identifier or if there was an error in the select operation.
		 */
		public function read() {
			if ($this->id < 1) {
				$this->log->error("Error reading role: Read called without non-zero identifier");

				throw new Exception("Cannot read role without non-zero identifier");
			}

			try {
				$stmt = $this->db->prepare("SELECT `Name` FROM `Role` WHERE `ID` = :id");
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				$stmt->execute();

				if ($stmt->rowCount() < 1) {
					$this->id = 0;
				} else {
					$row = $stmt->fetch();
					$this->name = $row['Name'];
				}
			} catch (PDOException $ex) {
				$this->log->error("Error reading role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to update a Role object in the database.
		 * 
		 * @throws Exception Thrown if called with 0 as identifier or if there was an error in the update operation.
		 */
		public function update() {
			if ($this->id < 1) {
				$this->log->error("Error updating role: Update called without non-zero identifier");

				throw new Exception("Cannot update role without non-zero identifier");
			}

			try {
				$stmt = $this->db->prepare("UPDATE `Role` SET `Name` = :name WHERE `ID` = :id");
				
				$stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				
				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error updating role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}

		/**
		 * Attempts to delete a Role object in the database.
		 * 
		 * @throws Exception Thrown if called with 0 as identifier or if there was an error in the delete operation.
		 */
		public function delete() {
			if ($this->id < 1) {
				$this->log->error("Error deleting role: Delete called without non-zero identifier");

				throw new Exception("Cannot delete role without non-zero identifier");
			}

			try {
				$stmt = $this->db->prepare("DELETE FROM `Role` WHERE `ID` = :id LIMIT 1");
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				$stmt->execute();
			} catch (PDOException $ex) {
				$this->log->error("Error deleting role: {MESSAGE}", array('MESSAGE' => $ex->getMessage()));

				throw $ex;
			}

			return;
		}
	}
