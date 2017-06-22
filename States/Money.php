<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22.06.17
 * Time: 6:18
 */
class Money extends Primitive_operator
{
    public function get_result()
    {
        $result = [];
        if ($this->result_ready) {
            if (trim($this->value)) {

                if(preg_match('/(-|\+|\*|\/) *(\d*)/', $this->value, $matches))
                {
                    $res = [
                        'operator' => 'MONEY',
                        'operation' => $matches[1],
                        'value' => $matches[2],
                    ];
                    $this->operator->set_default();
                    $result = $res;
                }
                else
                {
                    throw new MY_Exception("Incorrect MONEY operation");
                }

            } else {
                throw new MY_Exception("Unexpected semicolon");
            }
        }
        return $result;
    }

}