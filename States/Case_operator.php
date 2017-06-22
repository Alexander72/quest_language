<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22.06.17
 * Time: 6:24
 */

class Case_operator extends Primitive_operator
{
    private $case_source;
    private $case_title;
    private $result = [];
    private $state;
    private $bracket_counter = 0;

    private $line;
    private $pos;

    public function __construct()
    {
        parent::__construct();
        $this->state = 'DEFAULT';
    }


    protected function get_operator()
    {
        return 'CASE';
    }

    public function add_symbol($symbol)
    {
        global $debug;
        if ($this->state == 'DEFAULT' && trim($symbol))
        {
            if($symbol == '{') {
                $this->state = 'CASE_TITLE';
            }
             else {
                 throw new MY_Exception("Unexpected '".$symbol."'. Expected '{'");
             }

        }
        elseif($this->state == 'CASE_TITLE' && trim($symbol))
        {
            if($symbol == ':') {
                if(trim($this->case_title))
                    if(preg_match('/"(.*)"/', $this->case_title, $matches)){
                        $this->case_title = $matches[1];
                        $this->state = 'WAIT_CASE';
                    }
                    else{
                        throw new MY_Exception("Incorrect case name");
                    }
                else
                    throw new MY_Exception("Unexpected ':'. Expected case name");
            }
            elseif($symbol == '}') {
                $this->result_ready = true;
                $this->state = 'END';
                $this->operator->set_default();
            }
            else
                $this->case_title .= $symbol;
        }
        elseif($this->state == 'WAIT_CASE' && trim($symbol))
        {
            if($symbol == '{') {
                $this->line = $debug->get_line();
                $this->pos = $debug->get_position() + 1;
                $this->state = 'CASE';
            }
            else
                throw new MY_Exception("Unexpected '".$symbol."'. Expected '{'");

        }
        elseif($this->state == 'CASE')
        {
            if($symbol == '}')
                $this->bracket_counter--;
            
            if($symbol == '{')
                $this->bracket_counter++;

            if($this->bracket_counter == -1) {
                if(trim($this->case_source)) {
                    $this->operator->set_default();
                    $this->result['value'][$this->case_title] = parse($this->case_source, 1);
                    $this->case_source = '';
                    $this->case_title = '';
                    $this->bracket_counter = 0;
                    $debug->new_line($this->line);
                    $debug->new_pos($this->pos);
                    $this->state = 'CASE_TITLE';
                    $this->operator->set_operator('CASE');
                }
                else{
                    throw new MY_Exception("Unexpected '".$symbol."'");
                }
            }
            else{
                $this->case_source .= $symbol;
            }

        }
        elseif(trim($symbol)){
            throw new MY_Exception("Unexpected '".$symbol."'");
        }
    }

    public function result_ready()
    {
        return $this->state == 'END';
    }
    
    public function get_result()
    {
        $res = $this->result;
        $res['operator'] = 'CASE';
        $this->state = 'DEFAULT';
        $this->operator->set_default();
        return $res;
    }
}