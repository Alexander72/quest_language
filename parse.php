<?php
define('DEFAULT_STATE', 'DEFAULT');
include_once "MY_Exception.php";
include_once "Functions.php";
include_once "Debug.php";
include_once "Operator.php";
include_once "States/autoload.php";


$source_file = $argv[1];
$debug = new Debug($source_file);

$source = trim(file_get_contents($source_file));
$source_length = strlen($source);
$operator = new Operator();

function parse($source, $deep = 0)
{
	global $operator, $debug;
	$source_length = strlen($source);

	$result = [];
	$return = false;
	$i = 0;


	while( $i < $source_length )
	{
		switch ($operator->get_operator()) {
			case 'TEXT':
				if(!isset($text_inited))
					$text = new Text;

				$text_inited = true;

				$text->add_symbol($source[$i]);

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
	
				$case->add_symbol($source[$i]);
	
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

				$if->add_symbol($source[$i]);

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

				$include->add_symbol($source[$i]);

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

				$objects->add_symbol($source[$i]);

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

				$money->add_symbol($source[$i]);

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

				$tag->add_symbol($source[$i]);

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

				$goto->add_symbol($source[$i]);

				if($goto->result_ready())
				{
					$result[] = $goto->get_result();
					unset($goto_inited);
				}
				break;
			default:
				if(trim($source[$i]))
				{
					$operator->add_symbol_to_current_operator($source[$i]);
				}
				elseif(!$operator->is_default())
				{
					throw new MY_Exception("Unexpected ".$operator->get_operator_building(), $debug->get_position() - strlen($operator->get_operator_building()));
				}
				break;
		}
		if($source[$i] == "\n")
		{
			if(!$operator->allow_miltiline())
			{
				throw new MY_Exception("Expected ';'");
			}
			$debug->new_line();
		}
		$debug->new_pos();

		//need for recursion
		if($return)
			return $result;

		$i++;
	}

	if(!$operator->is_default()) {
		throw new MY_Exception("Unexpected " . $operator->get_operator_building(), $debug->get_position() - strlen($operator->get_operator_building()));
	}

	return $result;
}
$code = parse($source);
p($code, 1);
/*
$code = [	
	[
		'operator' => "TEXT",
		'value' =>"001.txt"
	], 
	[
		'operator' => "CASE",
		'value' => [
			'привет' => [
				[
					'operator' => "TEXT",
					'value' => "002.txt"
				],
				[
					'operator' => 'RAND',
					'value' => [	
						[
							'operator' => "TEXT",
							'value' =>"001.txt"
						], 	
						[
							'operator' => "TEXT",
							'value' =>"001.txt"
						], 	
						[
							'operator' => "TEXT",
							'value' =>"001.txt"
						]
					]
				],
				[
					'operator' => "CASE",
					'value' =>[
						'лошадка' => [
							[
								'operator' => "OBJECTS",
								'operation' => "+",
								'value' => 1,
								'item' => "копье"
							],
							[
								'operator' => "INCLUDE",
								'value' => "code1.txt"
							],
							[
								'operator' => "TAG",
								'value' => "TOPOR"
							],
							[
								'operator' => "IF",
								'condition' => "((a = 2 && v=3) || 2 > p + 2) && r < c",
								'THEN' => [
									[
										'operator' => "GOTO",
										'value' => "TOPOR"
									]
								],
								'ELSE' => [
									[
										'operator' => "MONEY",
										'operation' => "+",
										'value' => 1,
									]
								]
							]

						],
						'пол' => []
					]
				]
			],
			'лопата' => []
		]
	],
	[
		'operator' => "RAND",
			'value' => [
				'вася' => [],
				'петя' => [],
				'дима' => [],
			]
	]
];
$goto = [
	'TOPOR' => [
		'path' => [1, "value", "привет", 1, "value", "лошадка", 3]
	]
];
$f = fopen('parsed.json', "w");
fwrite($f, json_encode($code)."\n");
fwrite($f, json_encode($goto)."\n");
fclose($f);

$code = file_get_contents('parsed.json');

v(mb_convert_encoding($code, 'UTF-8'), 1);
*/