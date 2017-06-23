<?php

function p($var, $die = false)
{
	echo print_r($var, 1)."\n";
	if($die) die();
}

function v($var, $die = false)
{
	var_dump($var);
	if($die) die();
}

function lines_count($string)
{
	return substr_count($string, "\n");
}