<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22.06.17
 * Time: 16:28
 */
abstract class Recursive_operator extends Primitive_operator
{
    protected $states;
    protected $state;
    protected $debug;
    protected $result = [];

    public function __construct()
    {
        parent::__construct();

        global $debug;
        $this->debug = $debug;
        $this->state = 'DEFAULT';
        $this->set_states();
    }

    protected function set_states()
    {
        $this->states = [];
    }

    public function result_ready()
    {
        return $this->state == 'END';
    }

    protected function set_state($state)
    {
        if(in_array($state, array_keys($this->states)))
            $this->state = $state;
        else
            throw new Exception('Incorrect state \''.$state.'\'.');
    }

    public function add_symbol($symbol)
    {
        if(!isset($this->states[$this->state]['need_trim']) || $this->states[$this->state]['need_trim'] == true)
        {
            if(!trim($symbol)) {
                return;
            }
        }

        if(is_callable([$this,$this->states[$this->state]['method']]))
        {
            call_user_func_array([$this,$this->states[$this->state]['method']], [$symbol]);
        }
        else{
            throw new Exception('Dick, cunt, Djigurda!');
        }
    }

    public function get_result()
    {
        $res = $this->result;
        $res['operator'] = $this->get_operator();
        $this->set_state('DEFAULT');
        $this->operator->set_default();
        return $res;
    }

}