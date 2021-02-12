<?php

ini_set('display_errors', 'stderr');

include "errors.php";
include "patterns.php";
include "functions.php";

$codeFile = loadFile();
generateXML($codeFile);

$programXML = new SimpleXMLElement('<program></program>');
$programXML->addAttribute('language', 'IPPcode21');

//print($programXML->asXML());

?>