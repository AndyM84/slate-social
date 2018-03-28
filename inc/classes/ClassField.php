<?php

	/**
	 * Structure that describes a class field for
	 * query generation.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class ClassField {
		/**
		 * Key for property name on PHP class.
		 * 
		 * @var string
		 */
		public $classField;
		/**
		 * Key for column name on SQL table.
		 * 
		 * @var string
		 */
		public $dbColumn;
		/**
		 * Key for column parameter on SQL queries.
		 * 
		 * @var string
		 */
		public $dbParameter;
		/**
		 * PDO type value for SQL queries.
		 * 
		 * @var integer
		 */
		public $dbType;


		/**
		 * Instantiates a new ClassField structure.
		 * 
		 * @param string $classField String value of PHP object property name.
		 * @param string $dbColumn String value of SQL column name.
		 * @param string $dbParameter String value of SQL parameter name.
		 * @param integer $dbType Integer value of PDO column type.
		 */
		public function __construct($classField, $dbColumn, $dbParameter, $dbType) {
			$this->classField = $classField;
			$this->dbColumn = $dbColumn;
			$this->dbParameter = $dbParameter;
			$this->dbType = $dbType;

			return;
		}
	}

	/**
	 * Class to assist with larger SQL-bound objects
	 * by providing lists of data for use in query
	 * generation.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class ClassFields {
		/**
		 * Collection of fields.
		 *
		 * @var ClassField[]
		 */
		public $fields = array();


		/**
		 * Instantiates a new ClassFields collection.
		 *
		 * @param ClassField[] $fields
		 * @return void
		 */
		public function __construct(array $fields = null) {
			$this->fields = $fields;

			return;
		}

		/**
		 * Adds a field structure to the internal stack.
		 * 
		 * @param ClassField $field ClassField structure to add to stack.
		 */
		public function addField(ClassField $field) {
			$this->fields[] = $field;

			return;
		}

		/**
		 * Retrieves list of SQL column names from field
		 * stack with column names as keys.
		 * 
		 * @return array
		 */
		public function getSqlColumns() {
			$ret = array();

			if (count($this->fields) > 0) {
				foreach (array_values($this->fields) as $field) {
					$ret[$field->dbColumn] = true;
				}
			}

			return $ret;
		}

		/**
		 * Retrieves list of SQL parameter names from
		 * field stack with parameter names as keys
		 * and parameter types as values.
		 * 
		 * @return array
		 */
		public function getSqlParameters() {
			$ret = array();

			if (count($this->fields) > 0) {
				foreach (array_values($this->fields) as $field) {
					$ret[$field->dbParameter] = $field->dbType;
				}
			}

			return $ret;
		}

		/**
		 * Retrieves list of PHP object property names
		 * from field stack with property names as
		 * keys.
		 * 
		 * @return array
		 */
		public function getClassFields() {
			$ret = array();

			if (count($this->fields) > 0) {
				foreach (array_values($this->fields) as $field) {
					$ret[$field->classField] = true;
				}
			}

			return $ret;
		}
	}
