<?php
$output = '';
if (isset($_POST['submit_data']) && $_POST['submit_data'] == 'submitted')
{
$name = $_POST['_class_name'];
$properties = explode("\n", $_POST['_properties']);
$setters = isset($_POST['create_setters']) && $_POST['create_setters'] == 1 ? true : false;
$getters = isset($_POST['create_getters']) && $_POST['create_getters'] == 1 ? true : false;
$properties = array_map(function($n){return trim($n);}, $properties);

$output = array();
$output[] = '<?php';
$output[] = 'class '.$name.'{';
foreach ($properties as $p)
{
$output[] = "var $$p;";
}

if ($setters) {
    foreach ($properties as $p) {
        $output[] = "function set_$p(\$val){";
        $output[] = "\$this->$p = \$val;";
        $output[] = "}";
    }
}
if ($getters) {
    foreach ($properties as $p) {
        $output[] = "function get_$p(\$val){";
        $output[] = "return \$this->$p;";
        $output[] = "}";
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
                        <input type="text" name="_class_name" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label>Write properties one on each line</label>
                        <textarea class="form-control" name="_properties"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="create_setters" value="1">
                            <span class="form-check-label">Create Setters?</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="create_getters" value="1">
                            <span class="form-check-label">Create Getters?</span>
                        </label>
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
                <textarea class="form-control"><?php echo $output; ?></textarea>
            </fieldset>
        </div>
    </div>
</div>

</body>
</html>