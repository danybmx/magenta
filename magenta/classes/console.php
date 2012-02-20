<?php
/**
 * Console client for magenta
 *
 * We made an interface for shell
 * - input / output data
 * - execution of shell commands
 *
 * @author danybmx <dany@dpstudios.es>
 * @package Console
 */
class Console
{
	/**
	 * @var int $width cols of shell
	 * @var int $height lines of shell
	 */
	private $width = 80, $height = 24;

	/**
	 * Construct the class and get height and width of shell
	 */
	function __construct() {
		$this->width = $this->execute('tput cols');
		$this->height = $this->execute('tput lines');
	}

	/**
	 * Write text in the shell
	 *
	 * @param string $text text to write
	 * @param bool $return if true, insert an return at the end
	 */
	public function write($text, $return = true) {
		echo $text;
		if ($return) $this->cr();
	}

	/**
	 * Get text from shell
	 *
	 * @param string $prompt question
	 * @param mixed $valid valid answers (can be an array or 'is_file')
	 * @param string $default default value
	 * @return string value from shell
	 */
	public function read($prompt, $valid = null, $default = null) {
		if ($default)
			$prompt .= ' ['.$default.']';

		while ( ! isset($input) ||
				(is_array($valid) && ! in_array($input, $valid)) ||
				($valid == 'is_file' && ! is_file($input))) {
			$this->write($prompt);
			$input = strtolower(trim(fgets(STDIN)));
			if (empty($input) && ! empty($default))
				$input = $default;
		}
		return $input;
	}

	/**
	 * Function for execute a command in the shell
	 *
	 * @param string $command command to execute
	 * @param bool $s if true, use system instead of exec for get command response
	 * @return string response of the command
	 */
	public function execute($command, $s = false) {
		if ($s) {
			system($command);
		} else
			return exec($command);
	}

	/**
	 * Function for make a box and write text inside it (nice for titles)
	 *
	 * @param $text text for write inside the box
	 * @param null $c character for the line
	 */
	public function box($text, $c = '#') {
		$this->line($c);

		# Prepare string
		$lines = explode("\n", $text);
		foreach ($lines as $line) {
			$string = $c.' '.$line;
			while (strlen($string) < ($this->width - 1)) {
				$string .= ' ';
			}
			$string .= $c;
			$this->write($string);
		}

		$this->line($c);
	}

	/**
	 * Function for clear the shell
	 */
	public function clear() {
		if (strtolower(substr(PHP_OS, 0, 3)) == 'win')
			$this->execute('cls', true);
		else
			$this->execute('clear', true);
	}

	/**
	 * Function for write a complete line in shell
	 *
	 * @param string $c character for represent the line
	 * @param bool $return if true, insert an return at the end
	 */
	public function line($c = '#', $return = true) {
		for ($x = 0; $x < $this->width; $x++) {
			echo $c;
		}
		if ($return) $this->cr();
	}

	/**
	 * Function for insert cr in the shell
	 *
	 * @param int $n number or cr
	 */
	public function cr($n = 1) {
		for ($x = 0; $x < $n; $x++) {
			echo "\n";
		}
	}
}