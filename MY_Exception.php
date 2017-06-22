<?php

class MY_Exception extends Exception
{
	private $position;
	private $debug;

	function __construct($message, $pos = NULL, $line = NULL)
	{
		global $debug;
		$this->code = 0;
		$this->debug = $debug;
		//$this->file = $debug->get_file();
		//$this->line = $line ? $line : $debug->get_line();
		//$this->position = $pos ? $pos : $debug->get_position();
		$this->message = "Parse error: $message. At file ".$this->file." line ".$this->line." position ".$this->position.".";
	}
}