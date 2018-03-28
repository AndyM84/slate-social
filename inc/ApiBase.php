<?php

	namespace N2f;

	/**
	 * Class to ensure basic dependencies for
	 * API handler classes.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class ApiBase {
		/**
		 * Internal database instance.
		 * 
		 * @var \PDO
		 */
		protected $db = null;
		/**
		 * Internal logging instance.
		 * 
		 * @var Logger
		 */
		protected $log = null;


		/**
		 * Instantiates a new ApiBase object with
		 * dependencies included.
		 * 
		 * @param \PDO $db PDO connection resource for use by object.
		 * @param Logger $log Optional Logger instance to direct log messages to, if not supplied a new instance is created.
		 * @throws \Exception Thrown if a non PDO instance is provided for the DB dependency.
		 */
		public function __construct(\PDO $db, Logger $log = null) {
			if (!($db instanceof \PDO)) {
				throw new \Exception("Cannot instantiate with invalid PDO object");
			}

			$this->db = $db;
			$this->log = ($log !== null) ? $log : new Logger();

			return;
		}
	}
