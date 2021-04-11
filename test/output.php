<?php

/**
 * V dokumente vytvorí sekciu s výpisom vykonaných testov.
 * @param $id string id použité v odkaze na sekciu
 * @param $count_succ int počet úspešných testov
 * @param $count_total int celkový počet testov
 * @param $tables array meno adresára => instancia Table
 */
function createTestSummary($section_title, $id, $count_succ, $count_total, $tables) {
    global $doc, $html, $options;

    # vytvorenie section elementu
    $section = $html->appendChild($doc->createElement("section"));
    $section->setAttribute("id", $id);

    # nadpis
    $section->appendChild($doc->createElement("h2"))->nodeValue = $section_title;


    # text
    $text = $section->appendChild($doc->createElement("div"));
    $text->setAttribute("class", "text");

    # nazov skriptu
    $par = $text->appendChild($doc->createElement("p"));
    $strong = $par->appendChild($doc->createElement("strong"));
    $strong->nodeValue = "skript: ";
    $par->appendChild($doc->createTextNode($options["int-script"]));

    # celkova uspesnost
    $par = $text->appendChild($doc->createElement("p"));
    $strong = $par->appendChild($doc->createElement("strong"));
    $strong->nodeValue = "celková úspešnosť: ";
    $par = $par->appendChild($doc->createTextNode($count_succ."/".$count_total));

    # vygenerovanie a pridanie tabuliek pre kazdy adresar
    ksort($tables);

    foreach ($tables as $table) {
        $section->appendChild($table->getTitle());
        $section->appendChild($table->getTable());
    }
}

?>
