<?php

class each_property
{
    private ?string $accessibility = null;
    private ?string $type = null;
    private ?string $name = null;
    private ?string $default_value = null;
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
}

$output = '';
$class_name = '';
$class_properties = '';
$class_properties_format = '';
if (isset($_POST['submit_data']) && $_POST['submit_data'] == 'submitted') {
    $class_name = $_POST['_class_name'];
    $class_properties = $_POST['_properties'];
    $properties = explode("\n", $_POST['_properties']);
    $setters = isset($_POST['create_setters']) && $_POST['create_setters'] == 1 ? true : false;
    $getters = isset($_POST['create_getters']) && $_POST['create_getters'] == 1 ? true : false;
    $setters_type = isset($_POST['setters_type']) ? $_POST['setters_type'] : 'public';
    $getters_type = isset($_POST['getters_type']) ? $_POST['getters_type'] : 'public';
    $add_return_types = isset($_POST['add_return_types']) && $_POST['add_return_types'] == 1 ? $_POST['add_return_types'] : false;

    $properties = array_map(function ($n) {
        $parts = explode(' ', $n);
        $parts = array_map(function ($m) {
            return trim($m);
        }, $parts);

        return new each_property($parts);
    }, $properties);

    $output = array();
    $output[] = '<?php';
    $output[] = 'class ' . $class_name;
    $output[] = '{';
    foreach ($properties as $p) {
        $output[] = "\t".$p->get_line();
    }
    $output[] = "";
    $output[] = "\t"."public function __construct()";
    $output[] = "\t"."{";
    $output[] = "\t"."\t";
    $output[] = "\t"."}";

    if ($setters) {
        $output[] = '';
        $output[] = '';
        $output[] = "\t".'// Setter Methods';
        $output[] = '';
        foreach ($properties as $p) {
            $output[] = "\t".$setters_type . " function set_".$p->get_name()."(".($p->get_type() ? $p->get_type().' ' : '')."\$val)".($add_return_types ? ": ".$class_name : "");
            $output[] = "\t"."{";
            $output[] = "\t"."\t"."\$this->".$p->get_name()." = \$val;";
            $output[] = "\t"."\t"."return \$this;";
            $output[] = "\t"."}";
        }
    }
    if ($getters) {
        $output[] = '';
        $output[] = '';
        $output[] = "\t".'// Getter Methods';
        $output[] = '';
        foreach ($properties as $p) {
            $output[] = "\t".$getters_type . " function get_".$p->get_name()."()".($add_return_types && $p->get_type()  ? ": ".$p->get_type() : "");
            $output[] = "\t"."{";
            $output[] = "\t"."\t"."return \$this->".$p->get_name().";";
            $output[] = "\t"."}";
        }
    }

    $output[] = "}";

    $output[] = "?>";

    $output = implode(PHP_EOL, $output);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PHP Class Generator</title>
    <meta name="description" content="">
    <meta name="author" content="Tanmay Chakrabarty">

    <script type="text/javascript" src="assets/bootstrap/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="assets/bootstrap/bootstrap.min.css"/>
</head>

<body>
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>PHP Class Generator</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <fieldset>
                <legend>Configuration</legend>
                <form method="post">
                    <div class="form-group">
                        <label>Name of the class</label>
                        <input type="text" value="<?php echo $class_name; ?>" name="_class_name" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label>Write properties one on each line</label>
                        <p class="help-block">
                            properties_name<br/>
                            accessibility properties_name<br/>
                            accessibility type properties_name<br/>
                            accessibility type properties_name default_value<br/>
                        </p>
                        <textarea rows="20" class="form-control"
                                  name="_properties"><?php echo $class_properties; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="create_setters" value="1" <?php echo $setters ? 'checked' : ''; ?>>
                            <span class="form-check-label">Create Setters?</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Setters are ...</label>
                        <label class="form-radio-inline">
                            <input type="radio" class="form-radio-input" name="setters_type" value="public" checked>
                            <span class="form-radio-label">Public</span>
                        </label>
                        <label class="form-radio-inline">
                            <input type="radio" class="form-radio-input" name="setters_type" value="private">
                            <span class="form-radio-label">Private</span>
                        </label>
                        <label class="form-radio-inline">
                            <input type="radio" class="form-radio-input" name="setters_type" value="protected">
                            <span class="form-radio-label">Protected</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="create_getters" value="1" <?php echo $getters ? 'checked' : ''; ?>>
                            <span class="form-check-label">Create Getters?</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Getters are ...</label>
                        <label class="form-radio-inline">
                            <input type="radio" class="form-radio-input" name="getters_type" value="public" checked>
                            <span class="form-radio-label">Public</span>
                        </label>
                        <label class="form-radio-inline">
                            <input type="radio" class="form-radio-input" name="getters_type" value="private">
                            <span class="form-radio-label">Private</span>
                        </label>
                        <label class="form-radio-inline">
                            <input type="radio" class="form-radio-input" name="getters_type" value="protected">
                            <span class="form-radio-label">Protected</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="submit_data" value="submitted" class="btn btn-outline-success">
                            Generate
                        </button>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="add_return_types" value="1" <?php echo $add_return_types ? 'checked' : ''; ?>>
                            <span class="form-check-label">Add Return Types to setters/getters?</span>
                        </label>
                    </div>
                </form>
            </fieldset>
        </div>
        <div class="col-6">
            <fieldset>
                <legend>Output</legend>
                <textarea rows="30" class="form-control"><?php echo $output; ?></textarea>
            </fieldset>
        </div>
    </div>
</div>

</body>
</html>