<?php
class each_property
{
    private ?string $accessibility = null;
    private ?string $type = null;
    private ?string $name = null;
    private ?string $default_value = null;
    private ?string $db_column = null;
    private ?string $setter_name = null;
    private ?string $getter_name = null;

    private int $len = 0;
    function __construct(array $arr)
    {
        $this->len = count($arr);
        switch ($this->len) {
            case 1:
                $this->name = $arr[0];
                break;
            case 2:
                $this->accessibility = $arr[0];
                $this->name = $arr[1];
                break;
            case 3:
                $this->accessibility = $arr[0];
                $this->type = $arr[1];
                $this->name = $arr[2];
                break;
            case 4:
                $this->accessibility = $arr[0];
                $this->type = $arr[1];
                $this->name = $arr[2];
                $this->default_value = $arr[3];
                break;
            case 5:
                $this->accessibility = $arr[0];
                $this->type = $arr[1];
                $this->name = $arr[2];
                $this->default_value = $arr[3];
                $this->db_column = $arr[4];
                break;
        }
    }

    function get_line(): string
    {
        $output = '';
        switch ($this->len) {
            case 1:
                $output = "var $" . $this->name . ";";
                break;
            case 2:
                $output = $this->accessibility . " $" . $this->name . ";";
                break;
            case 3:
                $output = $this->accessibility . " " . $this->type . " $" . $this->name . ";";
                break;
            case 4:
            case 5:
                $output = $this->accessibility . " " . $this->type . " $" . $this->name . " = " . $this->default_value . ";";
                break;
        }
        return $output;
    }

    function get_name(): ?string
    {
        return $this->name;
    }
    function get_type(): ?string
    {
        return $this->type;
    }
    function get_db_column(): ?string
    {
        return $this->db_column;
    }
    function get_setter_name(): ?string
    {
        if(!$this->setter_name){
            $this->setter_name = 'set_'.$this->get_name();
        }

        return $this->setter_name;
    }
    function get_getter_name(): ?string
    {
        if(!$this->getter_name){
            $this->getter_name = 'get_'.$this->get_name();
        }

        return $this->getter_name;
    }
    function get_getter_method($getter_type, $add_return_types)
    {
        $out = "
        \t$getter_type function {$this->get_getter_name()}()".($add_return_types ? ": {$this->get_type()}" : "")."
        \t{
        \t\treturn \$this->{$this->get_name()};
        \t}
        ";

        return $out;
    }
    function get_setter_method($setter_type = '', $add_return_types = false, $class_name = '')
    {
        $out = "
        \t$setter_type function {$this->get_setter_name()}(".($this->get_type() ? $this->get_type().' ' : '')." \$val)".($add_return_types && $class_name ? ": ".$class_name : "")."
        \t{
        \t\t\$this->{$this->get_name()} = \$val;
        \t\treturn \$this;
        \t}
        ";

        return $out;
    }
}