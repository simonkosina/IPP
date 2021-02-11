<?php

include "errors.php";
include "patterns.php";
include "functions.php";

var_dump(loadFile());

$programXML = new SimpleXMLElement('<program></program>');
$programXML->addAttribute('language', 'IPPcode21');

//print($programXML->asXML());

?>