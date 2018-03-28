<?php

	namespace N2f;

	/**
	 * Class for common filesystem operations.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class IoHelper {
		/**
		 * Array to store cache of included files.
		 * 
		 * @var string[]
		 */
		private static $included = array();
		/**
		 * String that will replace '~' in paths.
		 * 
		 * @var string
		 */
		protected $corePath = null;


		/**
		 * Instantiates new IoHelper class.
		 * 
		 * @param string $corePath String value of core path, replaces '~' in paths.
		 * @throws \Exception Thrown if core path provided is invalid/non-existent.
		 */
		public function __construct($corePath) {
			if ($corePath === null || !is_dir($corePath)) {
				throw new \Exception("Invalid core path provided for IoHelper instance.");
			}

			$this->corePath = $corePath;

			return;
		}

		/**
		 * Copies a single file between paths if file exists at
		 * source and does not already exist at destination.
		 * 
		 * @param string $source String value of file source path, must exist and be non-null.
		 * @param string $dest String value of file destination path, must not exist and be non-null.
		 * @throws \Exception Thrown if source or destination are invalid, source doesn't exist, destination does exist, or copy operation fails.
		 */
		public function copyFile($source, $dest) {
			if (empty($source) || empty($dest)) {
				throw new \Exception("Invalid source or destination path provided to IoHelper::copyFile() -> " . $source . ", " . $dest);
			}

			if (substr($source, -1) == '/' || substr($dest, -1) == '/') {
				throw new \Exception("Neither source nor destination to IoHelper::copyFile() can be directories -> " . $source . ", " . $dest);
			}

			if (!$this->fileExists($source) || $this->fileExists($dest)) {
				throw new \Exception("Source file didn't exist or destination already exists in IoHelper::copyFile() -> " . $source . ", " . $dest);
			}

			if (!copy($this->processRoot($source), $this->processRoot($dest))) {
				throw new \Exception("Failed to copy source file, check PHP logs for more information -> " . $source);
			}

			return;
		}

		/**
		 * Copies an entire folder between paths if folder exists
		 * at source and does not exist at destination.
		 * 
		 * @param string $source String value of folder source path, must exist and be non-null.
		 * @param string $dest String value of folder destination path, must not exist and be non-null.
		 * @throws \Exception Thrown if source or destination are invalid, source doesn't exist, destination does exist, or any copy operation fails.
		 */
		public function copyFolder($source, $dest) {
			if (empty($source) || empty($dest)) {
				throw new \Exception("Invalid source or destination path provided to IoHelper::copyFolder() -> " . $source . ", " . $dest);
			}

			if (!$this->folderExists($source) || $this->folderExists($dest)) {
				throw new \Exception("Source directory didn't exist or destination already exists in IoHelper::copyFolder() -> " . $source . ", " . $dest);
			}

			$this->recursiveCopy($source, $dest);

			return;
		}

		/**
		 * Determines if a file exists at the given path.
		 * 
		 * @param string $path String value of potential file path.
		 * @return boolean True if file exists at path, false otherwise.
		 */
		public function fileExists($path) {
			if ($path !== null && !empty($path) && file_exists($this->processRoot($path))) {
				return true;
			}

			return false;
		}

		/**
		 * Determine if a folder exists at the given path.
		 * 
		 * @param string $path String value of potential folder path.
		 * @return boolean True if folder exists at path, false otherwise.
		 */
		public function folderExists($path) {
			if ($path !== null && !empty($path) && is_dir($this->processRoot($path))) {
				return true;
			}

			return false;
		}

		/**
		 * Retreives the contents of the file.
		 * 
		 * @param string $path String value of file path.
		 * @throws \Exception Thrown if file does not exist or path is invalid.
		 * @return string
		 */
		public function getContents($path) {
			if (!$this->fileExists($path)) {
				throw new \Exception("Non-existent file provided to IoHelper::getContents() -> " . $path);
			}

			return file_get_contents($this->processRoot($path));
		}

		/**
		 * Retrieves the stored core path value for this
		 * instance.
		 * 
		 * @return string
		 */
		public function getCorePath() {
			return $this->corePath;
		}

		/**
		 * Retrieves all file names in a folder non-recursively.
		 * 
		 * @param string $path  String value of folder path.
		 * @return \array|null
		 */
		public function getFolderFiles($path) {
			return $this->globFolder($path, 2);
		}

		/**
		 * Retrieves all folder names in a folder non-recursively.
		 * 
		 * @param string $path String value of folder path.
		 * @return \array|null
		 */
		public function getFolderFolders($path) {
			return $this->globFolder($path, 1);
		}

		/**
		 * Retrieves all item names in a folder with option to
		 * do so recursively.
		 * 
		 * @param string $path String value of folder path.
		 * @param boolean $recursive Boolean value to toggle recursive traversal, default is false.
		 * @return \array|null
		 */
		public function getFolderItems($path, $recursive = false) {
			return $this->globFolder($path, 0, $recursive);
		}

		/**
		 * Internal method to traverse a folder's contents with option
		 * to do so recursively.  Must specify return type via $globType
		 * parameter.
		 * 
		 * @param string $path String value of folder path.
		 * @param integer $globType Integer value of return type, can be 0 (all), 1 (folders only), and 2 (files only).
		 * @param boolean $recursive Boolean value to toggle recursive traversal, default is false.
		 * @return \array|null
		 */
		protected function globFolder($path, $globType, $recursive = false, $asMask = false) {
			if (empty($path)) {
				return null;
			}

			$ret = array();
			$path = $this->processRoot($path);

			if (!is_dir($path)) {
				return null;
			}

			if (substr($path, -1) != '/') {
				$path .= '/';
			}

			if ($dh = @opendir($path)) {
				while (($item = @readdir($dh)) !== false) {
					if ($item == '.' || $item == '..') {
						continue;
					}

					if (is_dir($path . $item) && $globType < 2) {
						$ret[] = $path . $item . '/';

						if ($recursive) {
							$tmp = $this->globFolder($path . $item, $globType, $recursive);

							if (count($tmp) > 0) {
								foreach (array_values($tmp) as $titem) {
									$ret[] = $titem;
								}
							}
						}
					} else if ($globType != 1) {
						$ret[] = $path . $item;
					}
				}
			}

			@closedir($dh);

			return $ret;
		}

		/**
		 * Attempts to load the given file as a PHP file. Caches
		 * all successful loads and by default will disallow reload.
		 * 
		 * @param string $path String value of file to attempt loading.
		 * @param boolean $allowReload Boolean value to allow reload if file has already been loaded, default is false.
		 * @throws \Exception Thrown if file doesn't exist or file has already been loaded and reloads are disallowed.
		 */
		public function load($path, $allowReload = false) {
			if (!$this->fileExists($path)) {
				throw new \Exception("Invalid file provided for IoHelper::load() -> " . $path);
			}

			if (isset(IoHelper::$included[$path]) && !$allowReload) {
				throw new \Exception("File has already been loaded -> " . $path);
			}

			IoHelper::$included[$path] = true;
			require($this->processRoot($path));

			return $path;
		}

		/**
		 * Attempts to create a folder if it doesn't exist.
		 * 
		 * @param string $path String value of path for folder to create.
		 * @return boolean
		 */
		public function makeFolder($path) {
			if (empty($path) || $this->folderExists($this->processRoot($path))) {
				return false;
			}

			return mkdir($this->processRoot($path));
		}

		/**
		 * Internal method to change '~' prefix into
		 * core path.
		 * 
		 * @param string $path String value of path to process.
		 * @return string
		 */
		protected function processRoot($path) {
			if ($path !== null && $path[0] == '~') {
				$path = $this->corePath . substr($path, ($path[1] == '/' && $this->corePath[strlen($this->corePath) - 1] == '/') ? 2 : 1);
			}

			return $path;
		}

		/**
		 * Attempts to write data to file at path.
		 * 
		 * @param string $path String value of file path.
		 * @param mixed $data Data to write to file, see http://php.net/file_put_contents for full details.
		 * @param integer $flags Optional flags to use for writing, see http://php.net/file_put_contents for full details.
		 * @param resource $context Optional stream context to use for writing, see http://php.net/file_put_contents for full details.
		 * @throws \Exception Thrown if file path is invalid or data is null.
		 * @return \boolean|integer
		 */
		public function putContents($path, $data, $flags = 0, $context = null) {
			if (empty($path)) {
				throw new \Exception("Invalid file provided to IoHelper::putContents() -> " . $path);
			}

			if ($data === null || empty($data)) {
				throw new \Exception("No data provided to IoHelper::putContents() -> " . $path);
			}

			if (($bytesWritten = @file_put_contents($this->processRoot($path), $data, $flags, $context)) !== false) {
				return $bytesWritten;
			}

			return false;
		}

		/**
		 * Internal method to traverse a folder's items
		 * recursively and copy them to a new destination.
		 * 
		 * @param string $source String value of source folder, must exist and be non-null.
		 * @param string $dest String value of destination folder, must not exist and be non-null.
		 * @throws \Exception Thrown if source doesn't exist, destination does exist, or an item copy operation fails.
		 */
		protected function recursiveCopy($source, $dest) {
			if (substr($source, -1) != '/') {
				$source .= '/';
			}

			if (substr($dest, -1) != '/') {
				$dest .= '/';
			}

			if (!$this->folderExists($source) || $this->folderExists($dest)) {
				throw new \Exception("Source directory didn't exist or destination directory does exist in IoHelper::recursiveCopy() -> " . $source . ", " . $dest);
			}

			$source = $this->processRoot($source);
			$dest = $this->processRoot($dest);

			$dh = @opendir($source);
			@mkdir($dest);

			while (($item = @readdir($dh)) !== false) {
				if ($item == '.' || $item == '..') {
					continue;
				}

				if (is_dir($source . $item)) {
					$this->recursiveCopy($source . $item, $dest . $item);
				} else {
					if (!copy($source . $item, $dest . $item)) {
						throw new \Exception("Failed to copy item in IoHelper::recursiveCopy() -> " . $source . ", " . $dest);
					}
				}
			}

			@closedir($dh);

			return;
		}
	}
