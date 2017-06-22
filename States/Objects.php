<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22.06.17
 * Time: 6:13
 */
class Objects extends Primitive_operator
{
    public function get_result()
    {
        $result = [];
        if ($this->result_ready) {
            if (trim($this->value)) {

                if(preg_match('/(-|\+|\*|\/) *(\d*) *"(.*)"/', $this->value, $matches))
                {
                    $res = [
                        'operator' => 'OBJECTS',
                        'operation' => $matches[1],
                        'value' => $matches[2],
                        'item' => $matches[3],
                    ];
                    $this->operator->set_default();
                    $result = $res;
                }
                else
                {
                    throw new MY_Exception("Incorrect OBJECTS operation");
                }

            } else {
                throw new MY_Exception("Unexpected semicolon");
            }
        }
        return $result;
    }

}