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

function generateArgs($XML, $rule, $instr) {
    global $err, $args;

    foreach ($rule as $instrIndex => $nonterm) {
        $correct = false;

        $matches = array();

        foreach ($args[$nonterm] as $type => $pattern) {
            $res = preg_match_all($pattern, $instr[$instrIndex+1], $matches);

            if ($res > 0) {
                $correct = true;
                break;
            }
        }

        if ($correct == false) {
            fprintf(STDERR, "Chybný argument inštrukcie: %s\n", $instr[0]);
            exit($err["other"]);
        }

        // Vygenerovanie elementu arg
        $argXML = $XML->addChild('arg'.($instrIndex+1));
        $argXML->addAttribute("type", $type);

        // Nastavenie textoveho elementu
        $text = $instr[$instrIndex + 1];

        // Ak argument je literal, odstrani sa typ a '@'
        if (strcmp($type, "label") != 0 &&
            strcmp($type, "type") != 0 &&
            strcmp($type, "var") != 0) {
            $text = preg_replace("/.*@/", "", $text);
        }

        $argXML[0] = $text;
    }
}

function generateXML($XML, $codeArr) {
    global $err, $header, $instructions;

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

        // Kontrola poctu argumentov, $instr obsahuje aj op. kod => count()-1
        if (count($instructions[$instr[0]]) != (count($instr)-1) ) {
            fprintf(STDERR, "Chybný počet argumentov inštrukcie: %s\n", $instr[0]);
            exit($err["other"]);
        }

        // Pridanie elementu instruction
        $instrXML = $XML->addChild("instruction");
        $instrXML->addAttribute("order", $key);
        $instrXML->addAttribute("opcode", $instr[0]);

        generateArgs($instrXML, $instructions[$instr[0]], $instr);
    }
}


?>