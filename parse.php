<?php
define('DEFAULT_STATE', 'DEFAULT');
include_once "MY_Exception.php";
include_once "Functions.php";
include_once "Debug.php";
include_once "Pointer.php";
include_once "Operator.php";
include_once "States/autoload.php";


$source_file = $argv[1];
$debug = new Debug($source_file);

$source = trim(file_get_contents($source_file));
$source_length = strlen($source);
$operator = new Operator;

$pointer = new Pointer;

function parse($source, $deep = 0)
{
	global $operator, $debug, $pointer;
	$source_length = strlen($source);

	$result = [];
	$pointer->reset();



	while( $pointer->get_pointer() < $source_length )
	{
		switch ($operator->get_operator()) {
			case 'TEXT':
				if(!isset($text_inited))
					$text = new Text;

				$text_inited = true;

				$text->add_symbol($source[$pointer->get_pointer()]);

				if($text->result_ready())
				{
					$result[] = $text->get_result();
					unset($text_inited);
				}
				break;
			case 'RAND':
			case 'CASE':
				if(!isset($case_inited))
					$case = new Case_operator($operator->get_operator());

				$case_inited = true;
	
				$case->add_symbol($source[$pointer->get_pointer()]);
	
				if($case->result_ready())
				{
					$result[] = $case->get_result();
					unset($case_inited);
				}
				break;
			case 'IF':
				if(!isset($if_inited))
					$if = new If_operator;

				$if_inited = true;

				$if->add_symbol($source[$pointer->get_pointer()]);

				if($if->result_ready())
				{
					$result[] = $if->get_result();
					unset($if_inited);
				}
				break;
			case 'INCLUDE':
				if(!isset($include_inited))
					$include = new Include_file();

				$include_inited = true;

				$include->add_symbol($source[$pointer->get_pointer()]);

				if($include->result_ready())
				{
					$result[] = $include->get_result();
					unset($include_inited);
				}
				break;
			case 'OBJECTS':
				if(!isset($objects_inited))
					$objects = new Objects();

				$objects_inited = true;

				$objects->add_symbol($source[$pointer->get_pointer()]);

				if($objects->result_ready())
				{
					$result[] = $objects->get_result();
					unset($objects_inited);
				}
				break;
			case 'MONEY':
				if(!isset($money_inited))
					$money = new Money();

				$money_inited = true;

				$money->add_symbol($source[$pointer->get_pointer()]);

				if($money->result_ready())
				{
					$result[] = $money->get_result();
					unset($money_inited);
				}
				break;
			case 'TAG':
			case '#':
				if(!isset($tag_inited))
					$tag = new Tag_operator();

				$tag_inited = true;

				$tag->add_symbol($source[$pointer->get_pointer()]);

				if($tag->result_ready())
				{
					$result[] = $tag->get_result();
					unset($tag_inited);
				}
				break;
			case 'GOTO':
				if(!isset($goto_inited))
					$goto = new Goto_operator();

				$goto_inited = true;

				$goto->add_symbol($source[$pointer->get_pointer()]);

				if($goto->result_ready())
				{
					$result[] = $goto->get_result();
					unset($goto_inited);
				}
				break;
			default:
				if(trim($source[$pointer->get_pointer()]))
				{
					$operator->add_symbol_to_current_operator($source[$pointer->get_pointer()]);
				}
				elseif(!$operator->is_default())
				{
					throw new MY_Exception("Unexpected '".$operator->get_operator_building()."'", $debug->get_position() - strlen($operator->get_operator_building()));
				}
				break;
		}
		if($source[$pointer->get_pointer()] == "\n")
		{
			if(!$operator->allow_miltiline())
			{
				throw new MY_Exception("Expected ';'");
			}
			$debug->new_line();
		}
		$debug->new_pos();


		$pointer->next();
	}

	if(!$operator->is_default()) {
		throw new MY_Exception("Unexpected '" . $operator->get_operator_building()."'", $debug->get_position() - strlen($operator->get_operator_building()));
	}

	return $result;
}

try {
	$code = parse($source);
}
catch (MY_Exception $e)
{
	$f = fopen('errors', "w");
	fwrite($f, $e->getMessage()."\n");
	fclose($f);

	$f = fopen('parsed.json', "w");
	fwrite($f, "");
	fclose($f);
}

if(isset($code))
{
	$f = fopen('errors', "w");
	fwrite($f, "");
	fclose($f);

	$f = fopen('parsed.json', "w");
	fwrite($f, json_encode($code)."\n");
	fclose($f);
}