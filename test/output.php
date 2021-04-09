<?php

/**
 * V dokumente vytvorí sekciu s výpisom vykonaných testov.
 * @param $id string id použité v odkaze na sekciu
 * @param $count_succ int počet úspešných testov
 * @param $count_total int celkový počet testov
 * @param $tables array meno adresára => instancia Table
 */
function createTestSummary($id, $count_succ, $count_total, $tables) {
    global $ul, $doc, $html, $options;

    # vytvorenie odkazu na danu sekciu
    $li = $ul->appendChild($doc->createElement("li"));
    $a = $li->appendChild($doc->createElement("a"));
    $a->setAttribute("href", "#" . $id);
    $a->setAttribute("class", "text");
    $a->nodeValue = $options["int-script"];

    # vytvorenie section elementu
    $section = $html->appendChild($doc->createElement("section"));
    $section->setAttribute("id", $id);

    # nadpis
    $section->appendChild($doc->createElement("h2"))->nodeValue = "Interpret";


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