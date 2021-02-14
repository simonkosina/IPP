<?php

ini_set('display_errors', 'stderr');

include "errors.php";
include "patterns.php";
include "functions.php";

// Inicializacia premennych pre zber udajov
$statLoc = 0;
$statComments = 0;
$statLabels = 0;
$statJumps = 0;
$statFWJumps = 0;
$statBackJumps = 0;
$statBadJumps = 0;

// Nacitanie vstupu
$codeFile = loadFile();

$statLoc = count($codeFile)-1; // Pocet instrukcii

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
echo "--loc=".$statLoc."\n";
echo "--comments=".$statComments."\n";

?>