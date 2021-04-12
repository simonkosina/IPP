<?php

/**
 * Vygeneruje tabulku s výsledkami pre jednotlivé adresáre.
 * @param $tables array meno adresára => instancia Table
 * @param $section DOMElement html sekcia, kde bude pridaná tabulka
 */
function createSummaryTable($tables, $section) {
    global $doc;

    # tabulka
    $table_el = $section->appendChild($doc->createElement("table"));
    $table_el->setAttribute("class", "table");

    # zahlavie
    $tr = $table_el->appendChild($doc->createElement("tr"));
    $th = $tr->appendChild($doc->createElement("th"));
    $th->nodeValue = "adresár";
    $th = $tr->appendChild($doc->createElement("th"));
    $th->nodeValue = "úspešnosť";

    # riadky
    foreach ($tables as $table) {
        $table_el->appendChild($table->getSummaryRow());
    }
}

/**
 * V dokumente vytvorí sekciu s výpisom vykonaných testov.
 * @param $tables array meno adresára => instancia Table
 * @param $section DOMElement html sekcia, kde budú pridané jednotlivé tabuľky
 */
function createTestTables($tables, $section) {
    # vygenerovanie a pridanie tabuliek pre kazdy adresar
    foreach ($tables as $table) {
        $section->appendChild($table->getTitle());
        $section->appendChild($table->getTable());
    }
}

/**
 * V dokumente vytvorí sekciu s výpisom vykonaných testov.
 * @param $scripts array mená použitých skriptov
 * @param $count_succ int počet úspešných testov
 * @param $count_total int celkový počet testov
 * @param $tables array meno adresára => instancia Table
 */
function generateResults($scripts, $count_succ, $count_total, $tables) {
    global $doc, $html;

    ksort($tables);

    # vytvorenie section elementu
    $section1 = $html->appendChild($doc->createElement("section"));

    # nadpis
    $section1->appendChild($doc->createElement("h2"))->nodeValue = "Súhrn výsledkov";

    # text
    $text = $section1->appendChild($doc->createElement("div"));
    $text->setAttribute("class", "text");

    # nazvy skriptov
    $par = $text->appendChild($doc->createElement("p"));
    $strong = $par->appendChild($doc->createElement("strong"));
    $strong->nodeValue = "testované skripty: ";

    foreach ($scripts as $key => $script) {
        if ($key == 0) {
            $par->appendChild($doc->createTextNode($script));
        } else {
            $par->appendChild($doc->createTextNode(", ".$script));
        }
    }

    # celkova uspesnost
    $par = $text->appendChild($doc->createElement("p"));
    $strong = $par->appendChild($doc->createElement("strong"));
    $strong->nodeValue = "celková úspešnosť: ";
    $par = $par->appendChild($doc->createTextNode($count_succ."/".$count_total));

    createSummaryTable($tables, $section1);

    # Tabulky pre jednotlive adresare
    $section2 = $html->appendChild($doc->createElement("section"));

    # nadpis
    $section2->appendChild($doc->createElement("h2"))->nodeValue="Výsledky testov pre jednotlivé adresáre";

    createTestTables($tables, $section2);
}

?>
