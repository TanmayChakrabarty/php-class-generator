<?php

include_once "each_property.class.php";

$output = '';
$class_name = '';
$class_properties = '';
$class_properties_format = '';
$setters = true;
$setters_type = 'public';
$getters = true;
$getters_type = 'public';
$add_return_types = true;
$add_features_for_edit_mode = true;
$the_table_name = '';
$the_primary_key = '';
if (isset($_POST['submit_data']) && $_POST['submit_data'] == 'submitted') {
    $class_name = $_POST['_class_name'];
    $class_properties = $_POST['_properties'];
    $properties = explode("\n", $_POST['_properties']);
    $setters = isset($_POST['create_setters']) && $_POST['create_setters'] == 1 ? true : false;
    $getters = isset($_POST['create_getters']) && $_POST['create_getters'] == 1 ? true : false;
    $setters_type = isset($_POST['setters_type']) ? $_POST['setters_type'] : 'public';
    $getters_type = isset($_POST['getters_type']) ? $_POST['getters_type'] : 'public';
    $add_return_types = isset($_POST['add_return_types']) && $_POST['add_return_types'] == 1 ? $_POST['add_return_types'] : false;
    $add_features_for_edit_mode = isset($_POST['add_features_for_edit_mode']) && $_POST['add_features_for_edit_mode'] == 1 ? $_POST['add_features_for_edit_mode'] : false;
    $the_table_name = $_POST['the_table_name'];
    $the_primary_key = $_POST['the_primary_key'];

    $properties = array_map(function ($n) {
        $parts = explode(' ', $n);
        $parts = array_map(function ($m) {
            return trim($m);
        }, $parts);

        return new each_property($parts);
    }, $properties);

    $output = array();
    $output[] = '<?php';
    $output[] = "\n";
    if($add_features_for_edit_mode){
        $output[] = 'use Dev\Core\CallReturn;';
        $output[] = 'use ezSQL_mysqli;';
        $output[] = "\n";
    }
    $output[] = 'class ' . $class_name;
    $output[] = '{';
    foreach ($properties as $p) {
        $output[] = "\t".$p->get_line();
    }
    if($add_features_for_edit_mode){
        $output[] = "\t"."private bool \$edit_data_not_found = false;";
        $output[] = "\t"."public function __construct(?int \$id = null)";
        $output[] = "\t"."{";
        $output[] = "\t\t"."if(\$id){";
        $output[] = "\t\t\t"."\$data = \$this->get_data(['id' => \$id, 'single' => true]);";
        $output[] = "\t\t\t"."if(\$data) \$this->fill_with_array(\$data);";
        $output[] = "\t\t\t"."else{";
        $output[] = "\t\t\t\t"."\$this->edit_data_not_found = true;";
        $output[] = "\t\t\t\t"."\$this->fill_defaults();";
        $output[] = "\t\t\t"."}";
        $output[] = "\t\t"."}";
        $output[] = "\t\t"."else \$this->fill_defaults();";
        $output[] = "\t"."}";

        $output[] = "\t"."private function fill_defaults()";
        $output[] = "\t"."{";
        $output[] = "\t\t"."//write you default filling functions";
        $output[] = "\t\t"."}";

        $output[] = "\t"."public function fill_with_array(array \$data)";
        $output[] = "\t"."{";
        $output[] = "\t\t"."//write you default filling functions";
        foreach($properties as $p){
            if($p->get_db_column()){
                $output[] = "\t\t".'$this->'.$p->get_setter_name().'($data[\''.$p->get_db_column().'\']);';
            }
        }
        $output[] = "\t\t"."}";

        $output[] = "\t"."public function is_invalid_edit(): bool";
        $output[] = "\t"."{";
        $output[] = "\t\t"."return \$this->edit_data_not_found;";
        $output[] = "\t\t"."}";

        $output[] = "\t".'public function get_data(?array $param = null)';
        $output[] = "\t"."{";
        $output[] = "\t\t".'$sql = "SELECT ';
        $output[] = "\t\t\t\t".'*';
        $output[] = "\t\t\t".'FROM ';
        $output[] = "\t\t\t\t".$the_table_name;
        $output[] = "\t\t\t".'WHERE 1 ';
        $output[] = "\t\t\t".'";';

        $output[] = "\t\t".'$count_sql = "SELECT COUNT('.$the_primary_key.') AS TOTAL';
        $output[] = "\t\t\t\t".'';
        $output[] = "\t\t\t".'FROM ';
        $output[] = "\t\t\t\t".$the_table_name;
        $output[] = "\t\t\t".'WHERE 1 ';
        $output[] = "\t\t\t".'";';

        $output[] = "\t\t".'$condition = "";';
        $output[] = "\t\t".'$db_map = [';
        foreach ($properties as $p){
            if($p->get_db_column()){
                $output[] = "\t\t\t"."'".$p->get_name()."' => '".$p->get_db_column()."',";
            }
        }
        $output[] = "\t\t".'];';
        $output[] = "\t\t".'return process_sql_operation($db_map, $condition, $sql, $count_sql, $param);';
        $output[] = "\t"."}";

        $output[] = "\t".'public function put_data()';
        $output[] = "\t"."{";
        $output[] = "\t\t".'/** @var ezSQL_mysqli $devdb */';
        $output[] = "\t\t".'global $devdb;';
        $output[] = "\t\t".'$ret = new CallReturn();';

        $output[] = "\t\t".'$insert_data = [';
        foreach ($properties as $p){
            if($p->get_db_column()){
                $output[] = "\t\t\t"."'".$p->get_db_column()."' => \$this->".$p->get_getter_name()."(),";
            }
        }

        $output[] = "\t\t".'];';

        $output[] = "\t\t".'if($this->get_id()) $db_ret = $devdb->insert_update(\''.$the_table_name.'\', $insert_data, " '.$the_primary_key.' = \'".$this->get_id()."\'");';
        $output[] = "\t\t".'else $db_ret = $devdb->insert_update(\''.$the_table_name.'\', $insert_data);';

        $output[] = "\t\t".'if($db_ret[\'error\']) $ret->add_error($db_ret[\'error\']);';
        $output[] = "\t\t".'else {';
        $output[] = "\t\t\t".'if(!$this->get_id()) $this->set_id($db_ret[\'success\']);';
        $output[] = "\t\t\t".'$ret->add_success($db_ret[\'success\']);';
        $output[] = "\t\t".'}';

        $output[] = "\t\t".'return $ret;';
        $output[] = "\t"."}";
    }
    else{
        $output[] = "";
        $output[] = "\t"."public function __construct()";
        $output[] = "\t"."{";
        $output[] = "\t"."\t";
        $output[] = "\t"."}";
    }


    if ($setters) {
        $output[] = '';
        $output[] = '';
        $output[] = "\t".'// Setter Methods';
        $output[] = '';
        foreach ($properties as $p) {
            $output[] = $p->get_setter_method($setters_type, $add_return_types, $class_name);
        }
    }
    if ($getters) {
        $output[] = '';
        $output[] = '';
        $output[] = "\t".'// Getter Methods';
        $output[] = '';
        foreach ($properties as $p) {
            $output[] = $p->get_getter_method($getters_type, $add_return_types);
        }
    }

    $output[] = "}";

    /*$output[] = "?>";*/

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
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="add_return_types" value="1" <?php echo $add_return_types ? 'checked' : ''; ?>>
                            <span class="form-check-label">Add Return Types to setters/getters?</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="add_features_for_edit_mode" value="1" <?php echo $add_features_for_edit_mode ? 'checked' : ''; ?>>
                            <span class="form-check-label">Features for edit mode?</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Table Name</label>
                        <input type="text" name="the_table_name" value="<?php echo $the_table_name; ?>" />
                    </div>
                    <div class="form-group">
                        <label>Primary Key</label>
                        <input type="text" name="the_primary_key" value="<?php echo $the_primary_key; ?>" />
                    </div>
                    <div class="form-group">
                        <button type="submit" name="submit_data" value="submitted" class="btn btn-outline-success">
                            Generate
                        </button>
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