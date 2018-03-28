<?php

	/**
	 * Base repository class.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package ZtcBaseAdmin
	 */
	class BaseRepo {
		/**
		 * Internal PDO instance.
		 * 
		 * @var PDO
		 */
		protected $db = null;
		/**
		 * Internal N2f\Logger instance.
		 * 
		 * @var N2f\Logger
		 */
		protected $log = null;


		/**
		 * Default ctor for repository instantiation.  Ensures
		 * 
		 * @param PDO $db PDO connection object to perform database operations.
		 * @param N2f\Logger $log Optional logger instance, if not supplied a new instance is created.
		 * @throws Exception Thrown if the PDO instance is null.
		 */
		public function __construct(PDO $db, N2f\Logger $log = null) {
			$this->db = $db;
			$this->log = ($log !== null) ? $log : new N2f\Logger();

			if ($this->db === null) {
				throw new Exception(ErrorStrings::DB_Not_Initialized);
			}

			return;
		}
	}