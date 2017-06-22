<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22.06.17
 * Time: 5:59
 */
abstract class Primitive_operator
{

    protected $value;
    protected $operator;
    protected $last_symbol;
    protected $result_ready;

    function __construct()
    {
        global $operator;
        $this->operator = $operator;
        $this->operator->set_operator($this->get_operator());
        $this->result_ready = false;
        $this->last_symbol = '';
    }

    protected function get_operator()
    {
        return strtoupper(get_class($this));
    }

    public function add_symbol($symbol)
    {
        if ($symbol !== ';') {
            $this->value .= $symbol;
        } else {
            if (!trim($this->value)) {
                throw new MY_Exception("Unexpected semicolon");
            }
            $this->operator->set_default();
            $this->result_ready = true;
        }

        $this->last_symbol = $symbol;
    }

    public function result_ready()
    {
        return $this->result_ready;
    }

    public function get_result()
    {
        $result = [];
        if ($this->result_ready) {
            if (trim($this->value)) {
                $this->operator->set_default();
                $result = ['operator' => $this->get_operator(), 'value' => trim($this->value)];
            } else {
                throw new MY_Exception("Unexpected semicolon");
            }
        }
        return $result;
    }
}