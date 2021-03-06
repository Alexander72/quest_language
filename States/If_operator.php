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

    private $path;

    public function __construct($path)
    {
        parent::__construct();
        $this->condition = '';
        $this->path = $path;
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
            'THEN' => ['recursive' => true, 'method' => 'then_state', 'need_trim' => false],
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
        elseif($symbol == "\n")
            throw new MY_Exception("Unexpected '\\n'. Expected ':'");
        else
        {
            $this->condition .= $symbol;
        }
    }

    protected function wait_then_state($symbol)
    {
        if($symbol == '{') {
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
                global $pointer;
                $this->operator->set_default();
                $stored_pointer = $pointer->get_pointer();

                $this->line = $this->debug->get_line();
                $this->pos = $this->debug->get_position() + 1;

                $this->debug->new_line($this->line - lines_count($this->then_source));
                $path = $this->path;
                $path[] = 'THEN';
                $res = parse($this->then_source, $path);
                $this->result['THEN'] = $res;

                $pointer->set_pointer($stored_pointer);

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
            global $pointer;
            $pointer->set_pointer($pointer->get_pointer() - 1);
            $this->set_state('END');
            $this->operator->set_default();
        }
    }
    
    protected function collect_else_state($symbol)
    {

        if($symbol == '{') {
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
                global $pointer;
                $this->operator->set_default();

                $this->line = $this->debug->get_line();
                $this->pos = $this->debug->get_position() + 1;

                $stored_pointer = $pointer->get_pointer();

                $this->debug->new_line($this->line - lines_count($this->else_source));
                $path = $this->path;
                $path[] = 'ELSE';
                $res = parse($this->else_source, $path);

                $pointer->set_pointer($stored_pointer);

                $this->debug->new_line($this->line);
                $this->debug->new_pos($this->pos);

                $this->result['ELSE'] = $res;
                $this->else_source = '';
                $this->else_title = '';
                $this->bracket_counter = 0;
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

        if(!isset($res['ELSE']))
            $res['ELSE'] = [];
        if(!isset($res['THEN']))
            $res['THEN'] = [];

        $res['operator'] = $this->get_operator();
        $res['condition'] = trim($this->condition);
        $this->set_state('DEFAULT');
        $this->operator->set_default();
        return $res;
    }


}