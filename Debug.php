<?php

class Debug
{
	private $file;
	private $line;
	private $pos;

	public function __construct($file)
	{
		$this->file = $file;
		$this->line = 1;
		$this->pos = 1;
	}

	public function new_line($line = false)
	{
		if($line)
			$this->line = $line;
		else
			$this->line++;
		$this->pos = 0;
	}

	public function new_pos($pos = false)
	{
		if($pos)
			$this->pos = $pos;
		else
			$this->pos++;
	}

	public function get_file()
	{
		return $this->file;
	}

	public function get_line()
	{
		return $this->line;
	}

	public function get_position()
	{
		return $this->pos;
	}
}