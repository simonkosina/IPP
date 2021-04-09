<?php

include_once "errors.php";
include_once "parse_arguments.php";
include_once "Test.php";
include_once "IntTest.php";
include_once "DirectoryFilter.php";
include_once "Table.php";
include_once "html_elements.php";

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

$doc = new DOMDocument;

# interpret
$int_tables = array(); # nazov adresara => instancia Table
$int_count_total = 0;
$int_count_succ = 0;

if (!$options["parse-only"]) {
    foreach ($iter_src as $file) {
        $name = $file[0];
        $dirname = dirname($name);

        if (!isset($int_tables[$dirname])) {
            $int_tables[$dirname] = new Table($dirname, $doc);
        }

        $test = new IntTest($name, $options["int-script"], $int_tables[$dirname]);

        $int_count_total++;

        if ($test->run()) {
            $int_count_succ++;
        };
    }
}

# HTML
$html = $doc->appendChild($doc->createElement("html"));

# meta info
$head = $html->appendChild($doc->createElement("head"));
$node = $head->appendChild($doc->createElement("meta"));
$node->setAttribute("charset", "UTF-8");

# title
$node = $head->appendChild($doc->createElement("title"));
$node->nodeValue = "test.php";

# style
$style = $html->appendChild($doc->createElement("style"));
$style->nodeValue = $style_string;

# header
$header = $html->appendChild($doc->createElement("header"));
$node = $header->appendChild($doc->createElement("h1"));
$node->nodeValue = "Výsledky testov";
$node = $header->appendChild($doc->createElement("p"));
$node->setAttribute("class", "text");
$node->nodeValue = "Kliknutím na riadok v tabuľke je možné zobraziť podrobnosti o teste.";

# testy
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
    $a->setAttribute("class", "text");
    $a->nodeValue = $options["int-script"];

    $int_section = $html->appendChild($doc->createElement("section"));
    $int_section->setAttribute("id", $int_id);

    $int_section->appendChild($doc->createElement("h2"))->nodeValue = "Interpret";

    $int_title = $int_section->appendChild($doc->createElement("div"));
    $int_title->setAttribute("class", "text");

    $int_p = $int_title->appendChild($doc->createElement("p"));
    $int_b = $int_p->appendChild($doc->createElement("strong"));
    $int_b->nodeValue = "skript: ";
    $int_p->appendChild($doc->createTextNode($options["int-script"]));

    $int_p = $int_title->appendChild($doc->createElement("p"));
    $int_b = $int_p->appendChild($doc->createElement("strong"));
    $int_b->nodeValue = "úspešnosť: ";
    $int_p = $int_p->appendChild($doc->createTextNode($int_count_succ."/".$int_count_total));


    ksort($int_tables);

    foreach ($int_tables as $table) {
        $int_section->appendChild($table->getTitle());
        $int_section->appendChild($table->getTable());
    }
}


# script
$script = $html->appendChild($doc->createElement("script"));
$script->nodeValue = $script_string;

$doc->formatOutput = true;
$out = $doc->saveHTML();

# nahradenie medzier
$out = str_replace("@emsp;", "&emsp;", $out);
echo $out;

?>