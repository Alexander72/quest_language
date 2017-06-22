<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22.06.17
 * Time: 19:25
 */
class Goto_operator extends Primitive_operator
{
    protected function get_operator()
    {
        return 'GOTO';
    }

    public function get_result()
    {
        $result = [];
        if ($this->result_ready) {
            if (preg_match('/#(.*)/', $this->value, $matches)) {
                $this->operator->set_default();
                $result = ['operator' => $this->get_operator(), 'value' => trim($matches[1])];
            } else {
                throw new MY_Exception("Unexpected semicolon");
            }
        }
        return $result;
    }

}