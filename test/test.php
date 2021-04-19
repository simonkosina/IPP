    <?php
/**
 * Súbor obsahuje definícu triedy ParseTest.
 *
 * @author Simon Košina, xkosin09
 */

include_once "errors.php";
include_once "parse_arguments.php";
include_once "Test.php";
include_once "IntTest.php";
include_once "ParseTest.php";
include_once "DirectoryFilter.php";
include_once "Table.php";
include_once "html_elements.php";
include_once "html_tables.php";

ini_set('display_errors', 'stderr');

# analyza parametrov
$options = parseArguments($argc, $argv);
$options = setDefaultParams($options);

# ziskanie testovacich suborov
try {
    $dir = new RecursiveDirectoryIterator($options["directory"]);
} catch (Exception $e) {
    fprintf(STDERR, $e->getMessage());
    exit(ERR_FILE_MISSING);
}

if ($options["recursive"]) {
    $iter_all = new RecursiveIteratorIterator($dir);
} else {
    $filtered = new DirectoryFilter($dir);
    $iter_all = new RecursiveIteratorIterator($filtered);
}

$iter_src = new RegexIterator($iter_all,'/^.+\.src$/i',RecursiveRegexIterator::GET_MATCH);

# vystupny dokument
$doc = new DOMDocument;

# vykonanie testov
$tables = array(); # nazov adresara => instancia Table
$count_total = 0;
$count_succ = 0;

#vykonanie testu pre kazdy subor
foreach ($iter_src as $file) {
	$name = $file[0];
	$dirname = dirname($name);

	# vytvorenie tabulky
	if (!isset($tables[$dirname])) {
		$tables[$dirname] = new Table($dirname, $doc);
	}

	# test pre dany subor

    if ($options["int-only"]) {
        $test = new IntTest($name, $options["int-script"], $tables[$dirname]);
    } elseif ($options["parse-only"]) {
        $test = new ParseTest($name, $options["parse-script"], $tables[$dirname], $options["jexamxml"], $options["jexamcfg"]);
    } else {
        $test = new Test($name, $options["parse-script"], $options["int-script"], $tables[$dirname]);
    }

	$count_total++;

	if ($test->run()) {
	    $count_succ++;
    }
}

# HTML
$html = $doc->appendChild($doc->createElement("html"));

# meta
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
$node->nodeValue = "Testovací rámec";
$node = $header->appendChild($doc->createElement("p"));
$node->setAttribute("class", "text");
$node->nodeValue = "Kliknutím na riadok v tabuľke je možné zobraziť podrobnosti o teste.";

# Vypis vysledku
$section = $html->appendChild($doc->createElement("section"));
if ($options["parse-only"]) {
    # analyzator
    generateResults([$options["parse-script"]], $count_succ, $count_total, $tables);
} elseif ($options["int-only"]) {
    # interpret
    generateResults([$options["int-script"]], $count_succ, $count_total, $tables);
} else {
    # analyza aj interpretacia
    generateResults([$options["parse-script"], $options["int-script"]], $count_succ, $count_total, $tables);
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
