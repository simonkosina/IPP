<?php

include_once "test_errors.php";
include_once "parse_arguments.php";

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
        # vytvorenie prikazu
        $cmd = "python3.8 ".$options["int-script"]." --source=".$name[0];
        $file_no_ext = substr($name[0], 0, -4);

        # pridanie inputu
        if (!file_exists($file_no_ext.".in")) {
            try {
                $file = fopen($file_no_ext.".in", "w");
                fclose($file);
            } catch (Exception $e) {
                echo $e->getMessage(), "\n";
                exit(ERR_FOPEN_OUT);
            }
        }

        # ocakavany rc
        

        $cmd = $cmd." --input=".$file_no_ext.".in";

        echo $cmd."\n";

        $out = array();
        $rc = 0;

        exec($cmd, $out, $rc);

        # porovnanie rc
    }
}
?>