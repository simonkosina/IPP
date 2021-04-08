<?php

include_once "errors.php";
include_once "parse_arguments.php";
include_once "Test.php";
include_once "IntTest.php";
include_once "DirectoryFilter.php";

ini_set('display_errors', 'stderr');

$options = parseArguments($argc, $argv);
$options = setDefaultParams($options);

try {
    $dir = new RecursiveDirectoryIterator($options["directory"]);
} catch (Exception $e) {
    echo $e->getMessage(), "\n";
    exit(ERR_FILE_MISSING);
}


# ziskanie suborov
if ($options["recursive"]) {
    $iter_all = new RecursiveIteratorIterator($dir);
} else {
    $filtered = new DirectoryFilter($dir);
    $iter_all = new RecursiveIteratorIterator($filtered);
}

$iter_src = new RegexIterator($iter_all,'/^.+\.src$/i',RecursiveRegexIterator::GET_MATCH);


# interpret
if (!$options["parse-only"]) {
    foreach ($iter_src as $file) {
        $name = $file[0];
        $test = new IntTest($name, $options["int-script"]);
        echo $name." : ".$test->run()."\n";
    }
}

# HTML5 vystup
$doc = new DOMDocument;
$html = $doc->appendChild($doc->createElement("html"));
$head = $html->appendChild($doc->createElement("head"));

# meta info
$node = $head->appendChild($doc->createElement("meta"));
$node->setAttribute("charset", "UTF-8");

# title
$node = $head->appendChild($doc->createElement("title"));
$node->nodeValue = "test.php";

# header
$header = $html->appendChild($doc->createElement("header"));
$node = $header->appendChild($doc->createElement("h1"));
$node->nodeValue = "Výsledky testov";

# skripty
$parse_id = "parse";
$int_id = "interpret";

$section = $html->appendChild($doc->createElement("section"));

$title = $section->appendChild($doc->createElement("h2"));
$title->nodeValue = "Testované skripty";

$nav = $section->appendChild($doc->createElement("nav"));
$ul = $nav->appendChild($doc->createElement("ul"));

if (!$options["int-only"]) {
    $li = $ul->appendChild($doc->createElement("li"));
    $a = $li->appendChild($doc->createElement("a"));
    $a->setAttribute("href", "#".$parse_id);
    $a->nodeValue = $options["parse-script"];
}

if (!$options["parse-only"]) {
    $li = $ul->appendChild($doc->createElement("li"));
    $a = $li->appendChild($doc->createElement("a"));
    $a->setAttribute("href", "#" . $int_id);
    $a->nodeValue = $options["int-script"];
}

$doc->formatOutput = true;
print $doc->saveHTML();

?>