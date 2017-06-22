<?php
class Operator
{
	private $operator;
	private $operator_building;
	private $operators;
	private $miltiline_operators;
	private $default_operator;

	public function __construct()
	{
		$this->operators = ['TEXT', 'CASE', 'RAND', 'IF', 'INCLUDE', 'OBJECTS', 'MONEY', 'TAG', 'GOTO'];
		$this->miltiline_operators = ['CASE', 'RAND', 'IF'];
		$this->default_operator = 'DEFAULT';
		$this->operator_building = '';
	}

	public function add_symbol_to_current_operator($symbol)
	{
		$this->operator_building .= $symbol;

		if(!$this->can_increase_to_operator())
			throw new MY_Exception('Unexpected \''.$this->operator_building.'\'');

		if($this->can_be_operator($this->operator_building))
		{
			$this->set_operator($this->operator_building);
			$this->operator_building = '';
		}
	}

	public function is_default()
	{
		return $this->operator === $this->default_operator;
	}

	public function get_operator_building()
	{
		return $this->operator_building;
	}

	public function set_operator($operator)
	{
		if($this->can_be_operator($operator))
		{
			$this->operator = strtoupper($operator);
		}
		else
		{
			throw new Exception("Uncorrect operator", 1);
			
		}
	}

	public function set_default()
	{
		$this->operator = $this->default_operator;
	}


	public function get_operator()
	{
		return $this->operator;
	}

	public function allow_miltiline()
	{
		return in_array(strtoupper($this->operator), $this->miltiline_operators) || $this->is_default();
	}

	private function can_be_operator($operator)
	{
		return in_array(strtoupper($operator), $this->operators) || $operator == '#';
	}

	private function can_increase_to_operator()
	{
		$str = $this->operator_building;

		if($str == "#")
			return true;

		foreach($this->operators as $operator)
		{
			if(preg_match('/^'.$str.'/', $operator))
				return true;
		}

		return false;
	}
}