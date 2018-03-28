<?php

	namespace N2f;

	/**
	 * Structure for representing usage option
	 * for scripts.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class ScriptUsageOption {
		/**
		 * Internal name (key) for option.
		 * 
		 * @var string
		 */
		public $name;
		/**
		 * Short name for option via CLI.
		 * 
		 * @var string
		 */
		public $shortName;
		/**
		 * Long name for option via CLI.
		 * 
		 * @var string
		 */
		public $longName;
		/**
		 * Example usage of option via CLI.
		 * 
		 * @var string
		 */
		public $example;
		/**
		 * Short description of option without
		 * usage explanation.
		 * 
		 * @var string
		 */
		public $shortDescription;
		/**
		 * Full description of option, including
		 * usage explanation(s).
		 * 
		 * @var string
		 */
		public $description;
		/**
		 * Whether or not the option is required.
		 * 
		 * @var boolean
		 */
		public $required;
	}

	/**
	 * Helper class to manage CLI script with
	 * options and automatic help generation.
	 * 
	 * @version 1.0
	 * @author Andrew Male (AndyM84)
	 * @package N2f
	 */
	class ScriptUsageHelper {
		/**
		 * Friendly name of script.
		 *
		 * @var string
		 */
		public $name;
		/**
		 * Description of script's functionality.
		 *
		 * @var string
		 */
		public $description;
		/**
		 * Collection of example strings for running the
		 * script.
		 *
		 * @var string[]
		 */
		private $examples = array();
		/**
		 * Collection of ScriptUsageOptions that work with
		 * the script.
		 *
		 * @var ScriptUsageOption[]
		 */
		private $options = array();

		/**
		 * Instantiates a new ScriptUsageHelper to manage automated
		 * display of script help.
		 *
		 * @param string $name Friendly name of script.
		 * @param string $description Description of script's functionality.
		 */
		public function __construct($name, $description) {
			$this->name = $name;
			$this->description = $description;

			return;
		}

		/**
		 * Adds an example usage to the basic help display.
		 *
		 * @param string $exampleString Full example text.
		 */
		public function addExample($exampleString) {
			$this->examples[] = $exampleString;

			return;
		}

		/**
		 * Adds an option to the script for display in basic
		 * and detailed help.
		 *
		 * @param string $name Friendly name of option.
		 * @param string $shortName Short name/argument for option.
		 * @param string $longName Long name/argument for option.
		 * @param string $example Sample usage for detailed help.
		 * @param string $shortDescription Short description for basic help.
		 * @param string $description Long description for detailed help.
		 * @param boolean $required Whether or not the argument is required by the script.
		 */
		public function addOption($name, $shortName, $longName, $example, $shortDescription, $description, $required = false) {
			$tmp = new ScriptUsageOption();
			$tmp->name = $name;
			$tmp->shortName = $shortName;
			$tmp->longName = $longName;
			$tmp->example = $example;
			$tmp->shortDescription = $shortDescription;
			$tmp->description = $description;
			$tmp->required = $required;

			$this->options[] = $tmp;

			return;
		}

		/**
		 * Determines if the console arguments satisfy
		 * the script's requirements.
		 *
		 * @param ConsoleHelper $consoleHelper Instance of ConsoleHelper with arguments populated.
		 * @return boolean True if all required fields are present or no field required, false otherwise.
		 */
		public function satisfiesRequirements(ConsoleHelper $consoleHelper) {
			foreach (array_values($this->options) as $opt) {
				if ($opt->required === true && !$consoleHelper->hasShortLongArg($opt->short, $opt->long, true)) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Displays the basic help screen with all options
		 * and examples.
		 *
		 * @param ConsoleHelper $consoleHelper Instance of ConsoleHelper with arguments populated.
		 * @param string $message Optional message to tell user why this help is being displayed.
		 */
		public function showBasicHelp(ConsoleHelper $consoleHelper, $message = null) {
			$consoleHelper->putLine($this->name . (($message !== null) ? ": {$message}" : ""));
			$consoleHelper->putLine("Try `php " . $consoleHelper->getSelf() . " -h` or `php " . $consoleHelper->getSelf() . " --help` for more information");

			return;
		}

		/**
		 * Attempts to display detailed help for an option, otherwise displays
		 * summary help.
		 *
		 * @param ConsoleHelper $consoleHelper Instance of ConsoleHelper with arguments populated.
		 */
		public function showOptionHelp(ConsoleHelper $consoleHelper) {
			$help = $consoleHelper->getParameterWithDefault('h', 'help');
			$consoleHelper->putLine($this->name);

			if ($help !== true) {
				$help = strtolower($help);

				foreach (array_values($this->options) as $opt) {
					if ($help == strtolower($opt->shortName) || $help == strtolower($opt->longName) || $help == strtolower($opt->name)) {
						$consoleHelper->putLine("Basic usage of {$opt->name} option");
						$consoleHelper->putLine();
						$consoleHelper->putLine($opt->description);
						$consoleHelper->putLine();

						$consoleHelper->putLine("Usage: php " . $consoleHelper->getSelf() . " --{$opt->shortName}");
						$consoleHelper->putLine("       php " . $consoleHelper->getSelf() . " --{$opt->longName}");
						$consoleHelper->putLine();

						$consoleHelper->putLine("Example:");
						$consoleHelper->putLine("   php " . $consoleHelper->getSelf() . "{$opt->example}");

						break;
					}
				}
			} else {
				$consoleHelper->putLine("Available options and example usage");
				$consoleHelper->putLine();

				$consoleHelper->putLine("Options:");
				$consoleHelper->putLine("  Either short or long versions are valid");

				$shortWidth = 1;
				$longWidth = 1;

				foreach (array_values($this->options) as $opt) {
					if (strlen($opt->shortName) > $shortWidth) {
						$shortWidth = strlen($opt->shortName);
					}

					if (strlen($opt->longName) > $longWidth) {
						$longWidth = strlen($opt->longName);
					}
				}

				foreach (array_values($this->options) as $opt) {
					$short = (!empty($opt->shortName)) ? "-" . str_pad($opt->shortName, $shortWidth) : str_pad("", $shortWidth + 1);
					$long = (!empty($opt->longName)) ? "--" . str_pad($opt->longName, $longWidth) : str_pad("", $longWidth + 2);

					$consoleHelper->putLine("    {$short} {$long}  {$opt->shortDescription}");
				}

				$consoleHelper->putLine();
				$consoleHelper->putLine("Examples:");

				foreach (array_values($this->options) as $opt) {
					$consoleHelper->putLine("    {$opt->example}");
				}

				foreach (array_values($this->examples) as $ex) {
					$consoleHelper->putLine("    php " . $consoleHelper->getSelf() . " {$ex}");
				}
			}

			return;
		}
	}
