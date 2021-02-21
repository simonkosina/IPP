<?php

function loadFile($stats) {
    global $comment;
    $file = array();

    while (($line = fgets(STDIN)) != false) {
        // Odstranenie komentarov a prebytocnych medzier
        $commentCount = 0;
        $noCommLine = preg_replace($comment, "", $line, -1, $commentCount);
        $noCommLine = trim($noCommLine);

        // Inkrementacia pocitadla ak riadok obsahoval komentar
        if ($commentCount >= 1) {
            $stats->incComments();
        }

        if (empty($noCommLine) == false) {
            $splitLine = preg_split("/ +/", $noCommLine);
            $splitLine[0] = strtoupper($splitLine[0]); // jednotna velkost kvoli indexovaniu
            array_push($file, $splitLine);
        }
    }

    $stats->setLoc($file);

    return $file;
}

function parseArgs() {
    $options = getopt("", ["help"]);
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
            $text = preg_replace("/[^@]*@/", "", $text, 1);
        }

        $argXML[0] = $text;
    }
}

function generateXML($XML, $codeArr, $stats) {
    global $err, $header, $instructions;

    if (preg_match($header, $codeArr[0][0]) != 1) {
        fprintf(STDERR, "Chybný zápis hlavičky zdrojového súboru.\n");
        exit($err["header"]);
    }

    foreach ($codeArr as $index => $instr) {
        $opcode = $instr[0];

        // Preskocenie hlavicky suboru
        if ($index == 0) {
            continue;
        }

        // Kontrola, ci v $instructions existuje kluc pre danu instrukciu
        if (!isset($instructions[$opcode])) {
            fprintf(STDERR, "Chybný operačný kód: %s\n", $opcode);
            exit($err["opcode"]);
        }

        // Kontrola poctu argumentov, $instr obsahuje aj op. kod => count()-1
        if (count($instructions[$opcode]) != (count($instr)-1) ) {
            fprintf(STDERR, "Chybný počet argumentov inštrukcie: %s\n", $opcode);
            exit($err["other"]);
        }

        // Pridanie elementu instruction
        $instrXML = $XML->addChild("instruction");
        $instrXML->addAttribute("order", $index);
        $instrXML->addAttribute("opcode", $opcode);

        generateArgs($instrXML, $instructions[$opcode], $instr);

        // Pocitanie navesti
        if (strcmp($opcode, "LABEL") == 0) {
            $stats->addLabel($instr[1]);
        }

        // Pocitanie skokov
        if (strcmp($opcode, "JUMP") == 0 ||
            strcmp($opcode, "JUMPIFEQ") == 0 ||
            strcmp($opcode, "JUMPIFNEQ") == 0 ||
            strcmp($opcode, "CALL") == 0) {
                $stats->addJump($instr[1]);
        }
    }
}


?>