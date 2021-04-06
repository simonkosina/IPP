<?php

include_once "test_errors.php";
include_once "parse_arguments.php";

ini_set('display_errors', 'stderr');

$options = parseArguments($argc, $argv);
$options = setDefaultParams($options);

var_dump($options);

//$dir = new RecursiveDirectoryIterator()

?>