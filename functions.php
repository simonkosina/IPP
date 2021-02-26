<?php

include_once "patterns.php";
include_once "Stats.php";

// Nacita kod zo STDIN, odstrani komentare a prazdne riadky.
// Vrati zoznam, kde kazda polozka je zoznam predstavujuci riadok kodu.
// Do $stats zapise pocet komentarov a prikazov.
// $stats - instancia triedy $Stats
function loadFile($stats): array{
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

// Vypis napovedy skriptu a ukoncenie programu.
function printHelp() {
    echo "hjelp\n";
    exit(ERR_OK);
}

// Vykona spracovanie argumentov prikazovej riadky.
// $stats - instancia $Stats
function parseArgs($stats)
{
    global $statsParam, $argv, $argc;

    // Ziadne parametre
    if ($argc == 1) {
        return;
    }

    // Parameter --help
    if (in_array("--help", $argv, true)) {
        // Musi byt pouzity samostatne
        if ($argc != 2) {
            fprintf(STDERR, "Zakázaná kombinácia parametrov.\n");
            exit(ERR_PARAM);
        }

        printHelp();
        exit(0);
    }

    // Parameter --stats
    $index = 1; // index do argv
    $file = ""; // nazov vystupneho suboru
    $isNew = false; // nacitanie noveho parametru --stats
    $firstIter = true;
    $error = false; // chybny argument
    $matches = array();

    while ($index < $argc) {
        $numMatches = preg_match_all($statsParam, $argv[$index], $matches, PREG_PATTERN_ORDER);

        // Novy parameter --stats
        if ($numMatches == 1) {
            $isNew = true;
            $index++; // Preskocenie 'stats=file'
            $file = $matches[2][0];
            $error = false;
        } else if ($error) {
            // Zly argument na vstupe
            fprintf(STDERR, "Neznámy parameter alebo zlá kombinácia parametrov.\n");
            exit(ERR_PARAM);
        } else if ($firstIter) {
            // Prvy parameter bol iny ako "stats=file"
            if ($argc != 2) {
                fprintf(STDERR, "Zakázaná kombinácia parametrov.\n");
                exit(ERR_PARAM);
            }
        }

        $firstIter = false;

        // Pridanie suboru
        if ($isNew) {
            $stats->addFile($file);
        }

        if ($index >= $argc) {
            break;
        }

        // Pridanie paremtrov
        switch ($argv[$index]) {
            case "--loc":
                $stats->addParams2File($file, "--loc");
                break;
            case "--comments":
                $stats->addParams2File($file, "--comments");
                break;
            case "--labels":
                $stats->addParams2File($file, "--labels");
                break;
            case "--jumps":
                $stats->addParams2File($file, "--jumps");
                break;
            case "--fwjumps":
                $stats->addParams2File($file, "--fwjumps");
                break;
            case "--backjumps":
                $stats->addParams2File($file, "--backjumps");
                break;
            case "--badjumps":
                $stats->addParams2File($file, "--badjumps");
                break;
            default:
                // Chybny argument, este jeden cyklus pre kontrolu
                // v pripade, ze sa jedna o dalsi parameter --stats
                $error = true;
        }

        if (!$error) {
            $index++;
        }

        $isNew = false;
    }
}

// Pomocna fcia pre generovanie jednotlivych XML elementov
// pre argumenty prikazov a ich kontrola.
// $XML - instancia SimpleXMLElement
// $rule - prvok zo zoznamu $instructions
// $isntr - spracovavana instrukcia
function generateArgs($XML, $rule, $instr) {
    global $args;

    foreach ($rule as $instrIndex => $nonterm) {
        $correct = false;

        $matches = array();

        foreach ($args[$nonterm] as $type => $pattern) {
            $res = preg_match_all($pattern, $instr[$instrIndex+1], $matches, PREG_PATTERN_ORDER);

            if ($res > 0) {
                $correct = true;
                break;
            }
        }

        if ($correct == false) {
            fprintf(STDERR, "Chybný argument inštrukcie: %s\n", $instr[0]);
            exit(ERR_OTHER);
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

// Kontrola vstupneho kodu a generovanie prislusneho XML vystupu.
// $XML - instancia SimpleXMLElement
// $codeArr - zoznam prikazov, vystup fcie loadFile()
// $stats - instancia triedy $Stats
function generateXML($XML, $codeArr, $stats) {
    global $header, $instructions;

    if (preg_match($header, $codeArr[0][0]) != 1) {
        fprintf(STDERR, "Chybný zápis hlavičky zdrojového súboru.\n");
        exit(ERR_HEADER);
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
            exit(ERR_OPCODE);
        }

        // Kontrola poctu argumentov, $instr obsahuje aj op. kod => count()-1
        if (count($instructions[$opcode]) != (count($instr)-1) ) {
            fprintf(STDERR, "Chybný počet argumentov inštrukcie: %s\n", $opcode);
            exit(ERR_OTHER);
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