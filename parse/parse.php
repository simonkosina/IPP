<?php

/* Parser IPPcode21
 * Autor: Simon Košina, xkosin09
 */

ini_set('display_errors', 'stderr');

include_once "errors.php";
include_once "patterns.php";
include_once "Stats.php";
include_once "functions.php";

$stats = new Stats();

parseArgs($stats);

// Nacitanie vstupu
$codeFile = loadFile($stats);

// Tvorba vysledneho XML
$programXML = new SimpleXMLElement('<program></program>');
$programXML->addAttribute('language', 'IPPcode21');
generateXML($programXML, $codeFile, $stats);

// Uprava XML
$domXML = dom_import_simplexml($programXML);
$dom = new DOMDocument("1.0", "UTF-8");
$domXML = $dom->importNode($domXML, true);
$dom->appendChild($domXML);
$dom->formatOutput = true;
$formattedXML = $dom->saveXML();

// Vypis statistik a XML
$stats->printStats();
echo $formattedXML;

?>
