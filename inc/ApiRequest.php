<?php

	namespace N2f;

	/**
	 * Enum of request types for API requests.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class ApiRequestType extends Enum {
		const DELETE = 1;
		const ERROR = 2;
		const GET = 3;
		const HEAD = 4;
		const OPTIONS = 5;
		const POST = 6;
		const PUT = 7;
	}

	/**
	 * Class to represent a single API request
	 * and provide meta information about the
	 * request to handler callbacks.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class ApiRequest {
		/**
		 * Name value of request type.
		 * 
		 * @var string
		 */
		private $requestTypeName = null;
		/**
		 * ApiRequestType value of request
		 * type.
		 * 
		 * @var ApiRequestType
		 */
		private $requestType = null;
		/**
		 * Whether or not request is deemed
		 * valid.
		 * 
		 * @var boolean
		 */
		private $isValid = false;
		/**
		 * Whether or not request has a
		 * JSON body payload.
		 * 
		 * @var boolean
		 */
		private $isJson = false;
		/**
		 * Any available cookie data for
		 * request.
		 * 
		 * @var mixed
		 */
		private $cookie = null;
		/**
		 * Any available server data for
		 * request.
		 * 
		 * @var mixed
		 */
		private $server = null;
		/**
		 * Any available input data for
		 * request. Can be JSON payload
		 * or from request variables.
		 * 
		 * @var mixed
		 */
		private $input = null;

		/**
		 * Instantiates a new ApiRequest object, pulling information
		 * automatically from the $_COOKIE and $_SERVER variables as
		 * well as determining request type and input type.
		 */
		public function __construct() {
			$this->cookie = new ParameterHelper($_COOKIE);
			$this->server = new ParameterHelper($_SERVER);
			$this->requestType = new ApiRequestType(ApiRequestType::ERROR);

			if (!$this->server->hasValue('REQUEST_METHOD')) {
				return;
			}

			switch (strtoupper($this->server->getString('REQUEST_METHOD'))) {
				case 'DELETE':
					$this->requestType = new ApiRequestType(ApiRequestType::DELETE);
					$this->requestTypeName = 'DELETE';

					break;
				case 'GET':
					$this->requestType = new ApiRequestType(ApiRequestType::GET);
					$this->requestTypeName = 'GET';

					break;
				case 'HEAD':
					$this->requestType = new ApiRequestType(ApiRequestType::HEAD);
					$this->requestTypeName = 'HEAD';

					break;
				case 'OPTIONS':
					$this->requestType = new ApiRequestType(ApiRequestType::OPTIONS);
					$this->requestTypeName = 'OPTIONS';

					break;
				case 'POST':
					$this->requestType = new ApiRequestType(ApiRequestType::POST);
					$this->requestTypeName = 'POST';

					break;
				case 'PUT':
					$this->requestType = new ApiRequestType(ApiRequestType::PUT);
					$this->requestTypeName = 'PUT';

					break;
				default:
					$this->requestType = new ApiRequestType(ApiRequestType::ERROR);
					$this->requestTypeName = 'ERROR';

					return;
			}

			try {
				if ($this->requestType->is(ApiRequestType::GET)) {
					$this->isValid = true;
				} else {
					if (empty($this->input)) {
						$this->readInput();
					}

					if (empty($this->input)) {
						return;
					}

					$this->isValid = true;

					json_decode(trim($this->input), true);

					if (($this->input[0] == '{' || $this->input[0] == '[') || json_last_error() == JSON_ERROR_NONE) {
						$this->isJson = true;
					}
				}
			} catch (\Exception $e) {
				return;
			}

			return;
		}

		/**
		 * Internal method to read the PHP input stream if possible.
		 */
		protected function readInput() {
			if (empty($this->input)) {
				try {
					if (($this->input = @file_get_contents("php://input")) === false) {
						$this->input = '';
					}
				} catch (\Exception $e) {
					$this->input = '';
				}
			}
		}

		/**
		 * Return a ParameterHelper instance with the contents
		 * of the $_GET global.
		 * 
		 * @return ParameterHelper
		 */
		public function getParameterizedGet() {
			return new ParameterHelper($_GET);
		}

		/**
		 * Returns the request type enum value if the request
		 * is valid.
		 * 
		 * @throws \Exception Thrown if request is invalid.
		 * @return ApiRequestType
		 */
		public function getRequestType() {
			if ($this->isValid === false) {
				throw new \Exception("Can't get request type on an invalid request.");
			}

			return $this->requestType;
		}

		/**
		 * Returns the name of the request type if the
		 * request is valid.
		 * 
		 * @throws \Exception Thrown if request is invalid.
		 * @return string
		 */
		public function getRequestTypeName() {
			if ($this->isValid === false) {
				throw new \Exception("Can't get request type on an invalid request.");
			}

			return $this->requestTypeName;
		}

		/**
		 * Returns the raw input string if the request is
		 * valid.
		 * 
		 * @throws \Exception Thrown if request is invalid.
		 * @return string
		 */
		public function getRawInput() {
			if ($this->isValid === false) {
				throw new \Exception("Can't get input on an invalid request.");
			}

			return $this->input;
		}

		/**
		 * Returns the request input in a ParameterHelper
		 * instance if the request is a GET type or if it
		 * has a JSON payload.
		 * 
		 * @throws \Exception Thrown if request is invalid or non-GET without JSON payload.
		 * @return ParameterHelper
		 */
		public function getParameterizedInput() {
			if ($this->isValid === false) {
				throw new \Exception("Can't get input on an invalid request.");
			}

			if ($this->requestType->is(ApiRequestType::GET)) {
				return new ParameterHelper($_GET);
			}

			if ($this->isJson !== true) {
				throw new \Exception("Can't get parameterized input for non-json payload.");
			}

			return new ParameterHelper(json_decode($this->input, true));
		}
	}
