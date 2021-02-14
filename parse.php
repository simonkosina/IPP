<?php

ini_set('display_errors', 'stderr');

include "errors.php";
include "patterns.php";
include "functions.php";

$codeFile = loadFile();

$programXML = new SimpleXMLElement('<program></program>');
$programXML->addAttribute('language', 'IPPcode21');
generateXML($programXML, $codeFile);

$domXML = dom_import_simplexml($programXML);
$dom = new DOMDocument("1.0", "UTF-8");
$domXML = $dom->importNode($domXML, true);
$dom->appendChild($domXML);

$dom->formatOutput = true;
$formattedXML = $dom->saveXML();

echo $formattedXML;

?>