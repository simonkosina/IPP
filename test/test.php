<?php

include_once "test_errors.php";
include_once "parse_arguments.php";
include_once "Test.php";
include_once "IntTest.php";

ini_set('display_errors', 'stderr');

$options = parseArguments($argc, $argv);
$options = setDefaultParams($options);

try {
    $dir = new RecursiveDirectoryIterator($options["directory"]);
} catch (Exception $e) {
    echo $e->getMessage(), "\n";
    exit(ERR_FOPEN_IN);
}


$iter = new RecursiveIteratorIterator($dir);
$re_iter = new RegexIterator($iter,'/^.+\.src$/i',RecursiveRegexIterator::GET_MATCH);

# interpret
if (!$options["parse-only"]) {
    foreach ($re_iter as $name) {

        $test = new IntTest($name[0], $options["int-script"]);
        echo $name[0]." : ".$test->run()."\n";
    }
}
?>