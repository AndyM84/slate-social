<?php

	namespace N2f;

	class BackendMenuStrings {
		const ElementKey = 'element';
		const SubElementKey = 'sub-elements';
	}

	/**
	 * A single backend menu element.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class BackendMenuElement {
		/**
		 * Internal key name for element.
		 * 
		 * @var string
		 */
		public $key;
		/**
		 * Displayed title of element.
		 * 
		 * @var string
		 */
		public $title;
		/**
		 * Href contents for element.
		 * 
		 * @var string
		 */
		public $href;
		/**
		 * Pattern to match for determining
		 * element active state.
		 * 
		 * @var string|array
		 */
		public $activeMatch;
		/**
		 * Icon class value for CSS.
		 * 
		 * @var string
		 */
		public $icon;

		/**
		 * Instantiates a new backend menu element.
		 * 
		 * @param string $key Internal identifier for element.
		 * @param string $title Displayed title for element.
		 * @param string $href Anchor href value for element.
		 * @param mixed $activeMatch Array or string of preg_match pattern for active matchine.
		 * @param mixed $icon Optional icon string.
		 */
		public function __construct($key, $title, $href, $activeMatch, $icon = null) {
			$this->key = $key;
			$this->title = $title;
			$this->href = $href;
			$this->activeMatch = $activeMatch;
			$this->icon = $icon;

			return;
		}

		/**
		 * Determines whether the element is active given a path.
		 * 
		 * @param string $currentPath Path to check against active match (recommend $_SERVER['REQUEST_URI']).
		 * @return boolean
		 */
		public function isActive($currentPath) {
			if (is_array($this->activeMatch)) {
				foreach (array_values($this->activeMatch) as $match) {
					if (preg_match($match, $currentPath) === 1) {
						return true;
					}
				}

				return false;
			}

			if (preg_match($this->activeMatch, $currentPath) === 1) {
				return true;
			}

			return false;
		}
	}

	/**
	 * Class to hold a backend menu setup.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class BackendMenuHelper {
		/**
		 * Internal store of menu elements.
		 * 
		 * @var array
		 */
		private $elements = array();

		/**
		 * Adds a top-level element for the menu.
		 * 
		 * @param string $key Internal identifier for element.
		 * @param string $title Displayed title for element.
		 * @param string $href Anchor href value for element.
		 * @param mixed $activeMatch Array or string of preg_match pattern for active matchine.
		 * @param mixed $icon Optional icon string.
		 * @throws \Exception 
		 * @return void
		 */
		public function addElement($key, $title, $href, $activeMatch, $icon = null) {
			if (array_key_exists($key, $this->elements) !== false) {
				throw new \Exception("Duplicate menu key registered: '{$key}'");
			}

			$this->elements[$key] = array(
				BackendMenuStrings::ElementKey => new BackendMenuElement($key, $title, $href, $activeMatch, $icon),
				BackendMenuStrings::SubElementKey => array()
			);

			return;
		}

		/**
		 * Adds a sub-level element for the menu.
		 * 
		 * @param string $parentKey Identifier for parent menu element.
		 * @param string $key Internal identifier for element.
		 * @param string $title Displayed title for element.
		 * @param string $href Anchor href value for element.
		 * @param mixed $activeMatch Array or string of preg_match pattern for active matchine.
		 * @param mixed $icon Optional icon string.
		 * @throws \Exception 
		 * @return void
		 */
		public function addSubElement($parentKey, $key, $title, $href, $activeMatch, $icon = null) {
			if (array_key_exists($parentKey, $this->elements) === false) {
				throw new \Exception("Invalid parent menu key: '{$parentKey}'");
			}

			if (array_key_exists($key, $this->elements[$parentKey][BackendMenuStrings::SubElementKey]) !== false) {
				throw new \Exception("Duplicate sub-menu key registered: '{$key}'");
			}

			$this->elements[$parentKey][BackendMenuStrings::SubElementKey][$key] = array(
				BackendMenuStrings::ElementKey => new BackendMenuElement($key, $title, $href, $activeMatch, $icon)
			);

			return;
		}

		/**
		 * Returns the current backend menu stack.
		 * 
		 * @return array[]
		 */
		public function getElements() {
			return $this->elements;
		}
	}
