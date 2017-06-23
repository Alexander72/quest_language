<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22.06.17
 * Time: 6:24
 */

class Case_operator extends Recursive_operator
{
    private $case_source;
    private $case_title;
    private $bracket_counter = 0;

    private $path;

    private $type;

    public function __construct($type, $path)
    {
        $this->path = $path;
        $this->type = $type;
        parent::__construct();
    }


    protected function get_operator()
    {
        return $this->type;
    }

    protected function set_states()
    {
        $this->states = [
            'DEFAULT' => ['start' => true, 'method' => 'default_state'],
            'WAIT_CASE_TITLE' => ['method' => 'wait_case_title'],
            'CASE_TITLE' => ['method' => 'case_title_state', 'need_trim' => false],
            'WAIT_CASE' => ['method' => 'wait_case_state'],
            'CASE' => ['recursive' => true, 'method' => 'case_state', 'need_trim' => false],
            'END' => ['finish' => true, 'method' => 'end_state'],
        ];
    }

    protected function default_state($symbol)
    {
        if($symbol == '{') {
            $this->set_state('WAIT_CASE_TITLE');
        }
        else {
            throw new MY_Exception("Unexpected '".$symbol."'. Expected '{'");
        }
    }

    protected function wait_case_title($symbol)
    {
        if($symbol == ':')
            throw new MY_Exception("Unexpected ':'. Expected case name");
        elseif($symbol == '}')
        {
            if($this->result) {
                $this->result_ready = true;
                $this->set_state('END');
                $this->operator->set_default();
            }
            else{
                throw new MY_Exception("Empty case");
            }
        }
        elseif($symbol != "\"")
            throw new MY_Exception("Unexpected '".$symbol."'. Expected case name");
        else
        {
            $this->set_state('CASE_TITLE');
            $this->case_title = $symbol;
        }
    }

    protected function case_title_state($symbol)
    {
        if($symbol == ':') {
            if(trim($this->case_title))
                if(preg_match('/^"(.*)"/', trim($this->case_title), $matches)){
                    $this->case_title = $matches[1];
                    $this->set_state('WAIT_CASE');
                }
                else{
                    throw new MY_Exception("Incorrect case name");
                }
            else
                throw new MY_Exception("Unexpected ':'. Expected case name");
        }
        elseif($symbol == "\n")
        {
            throw new MY_Exception("Unexpected \\n. Expected ':'");
        }
        else
            $this->case_title .= $symbol;
    }

    protected function wait_case_state($symbol)
    {
        if($symbol == '{') {
            $this->set_state('CASE');
        }
        else
            throw new MY_Exception("Unexpected '".$symbol."'. Expected '{'");
    }

    protected function case_state($symbol)
    {
        if($symbol == '}')
            $this->bracket_counter--;

        if($symbol == '{')
            $this->bracket_counter++;

        if($this->bracket_counter == -1) {
            if(trim($this->case_source)) {
                global $pointer;
                $this->operator->set_default();
                $stored_pointer = $pointer->get_pointer();

                $this->line = $this->debug->get_line();
                $this->pos = $this->debug->get_position() + 1;

                $this->debug->new_line($this->line - lines_count($this->case_source));
                $path = $this->path;
                $path[] = 'value';
                $path[] = $this->case_title;
                $res = parse($this->case_source, $path);
                $this->result['value'][$this->case_title] = $res;

                $pointer->set_pointer($stored_pointer);

                $this->case_source = '';
                $this->case_title = '';
                $this->bracket_counter = 0;
                $this->debug->new_line($this->line);
                $this->debug->new_pos($this->pos);
                $this->set_state('WAIT_CASE_TITLE');
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
    
}