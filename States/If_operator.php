<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22.06.17
 * Time: 16:10
 */
class If_operator extends Recursive_operator
{
    private $bracket_counter = 0;
    private $else_title;

    private $condition;
    private $then_source;
    private $else_source;

    public function __construct()
    {
        parent::__construct();
        $this->condition = '';
    }

    protected function get_operator()
    {
        return 'IF';
    }

    protected function set_states()
    {
        $this->states = [
            'DEFAULT' => ['start' => true, 'method' => 'default_state', 'need_trim' => false],
            'WAIT_THEN' => ['method' => 'wait_then_state'],
            'THEN' => ['recursive' => true, 'method' => 'then_state'],
            'WAIT_ELSE' => ['method' => 'wait_else_state'],
            'COLLECT_ELSE' => ['method' => 'collect_else_state'],
            'ELSE' => ['recursive' => true, 'method' => 'else_state', 'need_trim' => false],
            'END' => ['finish' => true, 'method' => 'end_state'],
        ];
    }
    
    protected function default_state($symbol)
    {
        if($symbol == ':')
        {
            $this->set_state('WAIT_THEN');
        }
        else
        {
            $this->condition .= $symbol;
        }
    }

    protected function wait_then_state($symbol)
    {
        if($symbol == '{') {
            $this->line = $this->debug->get_line();
            $this->pos = $this->debug->get_position() + 1;
            $this->set_state('THEN');
        }
        else
            throw new MY_Exception("Unexpected '".$symbol."'. Expected '{'");
    }

    protected function then_state($symbol)
    {
        if($symbol == '}')
            $this->bracket_counter--;

        if($symbol == '{')
            $this->bracket_counter++;

        if($this->bracket_counter == -1) {
            if(trim($this->then_source)) {
                $this->operator->set_default();
                $res = parse($this->then_source, 1);
                $this->result['then'] = $res;
                $this->then_source = '';
                $this->bracket_counter = 0;
                $this->debug->new_line($this->line);
                $this->debug->new_pos($this->pos);
                $this->set_state('WAIT_ELSE');
                $this->operator->set_operator('IF');
            }
            else{
                throw new MY_Exception("Unexpected '".$symbol."'");
            }
        }
        else{
            $this->then_source .= $symbol;
        }
    }


    protected function wait_else_state($symbol)
    {
        if($symbol == 'E') {
            $this->else_title = 'E';
            $this->set_state('COLLECT_ELSE');
        }
        else
        {
            /** @TODO сделать счетчик цикла объектом и сдвинуть его здесь на 1 назад */
            $this->operator->set_default();
        }
    }
    
    protected function collect_else_state($symbol)
    {

        if($symbol == '{') {
            $this->line = $this->debug->get_line();
            $this->pos = $this->debug->get_position() + 1;
            $this->set_state('ELSE');
        }
        else
        {
            $this->else_title .= $symbol;
        }
    }


    protected function else_state($symbol)
    {
        if($this->else_title !== 'ELSE')
            throw new MY_Exception("Unexpected '".$symbol."'");

        if($symbol == '}')
            $this->bracket_counter--;

        if($symbol == '{')
            $this->bracket_counter++;

        if($this->bracket_counter == -1) {
            if(trim($this->else_source)) {
                $this->operator->set_default();
                $res = parse($this->else_source, 1);
                $this->result['else'] = $res;
                $this->else_source = '';
                $this->else_title = '';
                $this->bracket_counter = 0;
                $this->debug->new_line($this->line);
                $this->debug->new_pos($this->pos);
                $this->set_state('END');
                $this->operator->set_default();
            }
            else{
                throw new MY_Exception("Unexpected '".$symbol."'");
            }
        }
        else{
            $this->else_source .= $symbol;
        }
    }

    public function get_result()
    {
        $res = $this->result;
        $res['operator'] = $this->get_operator();
        $res['condition'] = $this->condition;
        $this->set_state('DEFAULT');
        $this->operator->set_default();
        return $res;
    }


}