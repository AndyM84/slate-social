<?php

	namespace N2f;

	/**
	 * Class to represent an HTML
	 * meta element.
	 * 
	 * @version 1.0
	 * @author Andrwe Male (AndyM84)
	 * @package N2f
	 */
	class PageMetaElement {
		private $name;
		private $content;


		/**
		 * Instantiates a new PageMetaElement
		 * object.
		 * 
		 * @param string $name Name of the meta element.
		 * @param string $content Content of the meta element.
		 */
		public function __construct($name, $content) {
			$this->name = $name;
			$this->content = $content;

			return;
		}

		/**
		 * Renders the meta element into its HTML
		 * equivalent, optionally returned as a
		 * string.
		 * 
		 * @param boolean $return Optional toggle to trigger returning the HTML as a string.
		 * @return string
		 */
		public function render($return = false) {
			$rendered = "<meta name=\"{$this->name}\" content=\"{$this->content}\" />";

			if ($return === true) {
				return $rendered;
			}

			echo($rendered);

			return "";
		}
	}

	/**
	 * Class to hold basic information
	 * on a page, one per request.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class Page {
		/**
		 * Array of content block strings.
		 * 
		 * @var array
		 */
		protected $blocks = array();
		/**
		 * Page title as string.
		 * 
		 * @var string
		 */
		protected $title = null;
		/**
		 * Array of meta elements.
		 * 
		 * @var PageMetaElement[]
		 */
		protected $meta = array();


		/**
		 * Add a meta element to the stack.
		 * 
		 * @param PageMetaElement $elem Element to add to stack.
		 * @throws \Exception Thrown if a null element is provided.
		 */
		public function addMeta(PageMetaElement $elem) {
			if ($elem === null) {
				throw new \Exception("Cannot add a null meta element");
			}

			$this->meta[] = $elem;

			return;
		}

		/**
		 * Retrieves the element stack.
		 * 
		 * @return PageMetaElement[]
		 */
		public function getMeta() {
			return $this->meta;
		}

		/**
		 * Starts capturing a content block by starting
		 * the output buffer and initializing the content
		 * block in the stack if necessary.
		 * 
		 * @param mixed $key String key for name of content block.
		 */
		public function startBlock($key) {
			if (!array_key_exists($key, $this->blocks)) {
				$this->blocks[$key] = array();
			}

			ob_start();

			return;
		}

		/**
		 * Stops capturing a content block by ending the
		 * output buffer and storing the contents in the
		 * block stack.
		 * 
		 * @param string $key String key for name of content block.
		 * @throws \Exception Thrown if the named content block hasn't been initialized in stack.
		 */
		public function endBlock($key) {
			if (!array_key_exists($key, $this->blocks)) {
				throw new \Exception("Cannot end a block that hasn't been started");
			}

			$contents = ob_get_contents();
			ob_end_clean();

			$this->blocks[$key][] = $contents;

			return;
		}

		/**
		 * Renders a content block, either as direct output
		 * or as a string to be returned.
		 * 
		 * @param string $key String key for name of content block.
		 * @param boolean $return Optional toggle to return the content block as a string.
		 * @param string $indent Optional string to use for string indentation, defaults to a single tab.
		 * @throws \Exception Thrown if named content block hasn't been initialized in stack.
		 * @return string Optionally returns content block as a string.
		 */
		public function renderBlock($key, $return = false, $indent = "\t") {
			if (!array_key_exists($key, $this->blocks)) {
				throw new \Exception("Cannot display a block that doesn't exist");
			}

			$glue = "\n" . $indent;
			$content = $indent . implode($glue, $this->blocks[$key]);

			if ($return === true) {
				return $content;
			}

			echo($content);

			return "";
		}

		/**
		 * Sets the title for this page.
		 * 
		 * @param string $title String value of page title.
		 */
		public function setTitle($title) {
			$this->title = $title;

			return;
		}

		/**
		 * Renders the page title as a proper HTML
		 * title.
		 * 
		 * @param boolean $return Optional toggle to return HTML title as string.
		 * @return string Optionally returns rendered HTML title.
		 */
		public function renderTitle($return = false) {
			$title = "<title>{$this->title}</title>";

			if ($return === true) {
				return $title;
			}

			echo($title);

			return "";
		}
	}
