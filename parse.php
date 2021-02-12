<?php

ini_set('display_errors', 'stderr');

include "errors.php";
include "patterns.php";
include "functions.php";

$codeFile = loadFile();

//var_dump($codeFile);

$programXML = new SimpleXMLElement('<program></program>');
$programXML->addAttribute('language', 'IPPcode21');
generateXML($programXML, $codeFile);

print($programXML->asXML());

?>