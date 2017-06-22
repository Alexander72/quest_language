<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22.06.17
 * Time: 20:15
 */
class Pointer
{
    private $pointer;

    function __construct()
    {
        $this->pointer = 0;
    }

    function __toString()
    {
        return (string)$this->pointer;
    }

    public function next()
    {
        $this->pointer++;
    }

    public function set_pointer($pointer)
    {
        $this->pointer = $pointer;
    }

    public function get_pointer()
    {
        return $this->pointer;
    }

    public function reset()
    {
        $this->pointer = 0;
    }
}