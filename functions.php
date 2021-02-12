<?php

function loadFile() {
    global $comment;
    $file = array();

    while (($line = fgets(STDIN)) != false) {
        // Odstranenie komentarov a prebytocnych medzier
        $noCommLine = preg_replace($comment, "", $line);
        $noCommLine = trim($noCommLine);

        if (empty($noCommLine) == false) {
            $splitLine = preg_split("/ +/", $noCommLine);
            $splitLine[0] = strtoupper($splitLine[0]); // jednotna velkost kvoli indexovaniu
            array_push($file, $splitLine);
        }
    }

    return $file;
}

function generateXML($codeArr) {
    global $err, $header, $instructions, $args;

    if (preg_match($header, $codeArr[0][0]) != 1) {
        fprintf(STDERR, "Chybný zápis hlavičky zdrojového súboru.\n");
        exit($err["header"]);
    }

    foreach ($codeArr as $key => $instr) {
        // Preskocenie hlavicky suboru
        if ($key == 0) {
            continue;
        }

        // Kontrola, ci v $instructions existuje kluc pre danu instrukciu
        if (!isset($instructions[$instr[0]])) {
            fprintf(STDERR, "Chybný operačný kód: %s\n", $instr[0]);
            exit($err["opcode"]);
        }

    }
}


?>