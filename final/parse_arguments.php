<?php

/**
 * Súbor obsahujúci funkcie potrebné pre analýzu parametrov.
 *
 * @Author: Simon Košina, xkosin09
 */

/**
 * Funkcia vypíše nápovedu na štandardný výstup.
 */
function printHelp() {
    echo "použitie: [--help] [--directory path] [--recursive] [--parse-script=file] [--int-script=file] [--parse-only] ";
    echo "[--int-only] [--jexamxml=file] [--jexamcfg=file]\n\n";
    echo "Nástroj pre testovanie skriptov pre analýzu a interpretáciu zdrojového kódu v jazyku IPPcode21.\n\n";
    echo "voliteľné parametre:\n";
    echo "  --help\t\tvypíše tento text a ukončí program\n";
    echo "  --directory path \tcesta k adresáru obsahujúcemu súbory na testovanie\n";
    echo "  --recursive\t\tzapnutie rekurzívneho prehľadávania podadresárov\n";
    echo "  --parse-script file\tnázov súboru obsahujúci skript v PHP 7.4 pre analýzu zdrojového kódu\n";
    echo "  --int-script file\tnázov súboru obsahujúci skript v Python 3.8 pre interpret XML reprezentácie zdrojového kódu\n";
    echo "  --parse-only\t\tbude testovaný iba skript pre analýzu zdrojového kódu v IPPcode21\n";
    echo "  --int-only\t\tbude testovaný iba skript pre interpret XML reprezentácie kódu v IPPcode21\n";
    echo "  --jexamxml file\tsúbor s JAR balíčkom s nástrojom A7Soft JExamXML\n";
    echo "  --jexamcfg file\tsúbor s konfiguráciou nástroja A7Soft JExamXML\n";
}

/**
 * Funckia analyzuje parametre. Pri chybne uvedených parametroch ukončí vykonávanie programu s odpovedajúcim návratovým kódom.
 * @param $argc int počet argumentov
 * @param $argv array zoznam argumentov
 * @return array výsledok volania funkcie getopts
*/
function parseArguments($argc, $argv) {
    # parsovanie argumentov
    $long_opts = [
        "help",
        "directory:",
        "recursive",
        "parse-script:",
        "int-script:",
        "parse-only",
        "int-only",
        "jexamxml:",
        "jexamcfg:"
    ];

    $options = getopt("", $long_opts);

    # kontrola zakazanych kombinacii parametrov
    $invalid_params = false;

    if (isset($options["help"]) && $argc != 2) {
        $invalid_params = true;
    } elseif (isset($options["help"])) {
        printHelp();
        exit(ERR_OK);
    }

    if ((isset($options["int-only"]) && isset($options["parse-only"]))
        || (isset($options["int-only"]) && isset($options["parse-script"]))
        || (isset($options["parse-only"]) && isset($options["int-script"]))) {
        $invalid_params = true;
    }

    if ($invalid_params) {
        fprintf(STDERR, "Zakázaná kombinácia parametrov.\n");
        exit(ERR_PARAM);
    }

    # kontrola neznamych parametrov
    if (count($options) + 1 != $argc) {
        fprintf(STDERR, "Neznámy parameter.\n");
        exit(ERR_PARAM);
    }

    return $options;
}

/**
 * Funkcia doplní do vstupného zoznamu implicitné hodnoty neuvedených parametrov.
 * Položky predstavujúce parametre uvádzané bez hodnôt, budú obsahovať hodnoty true/false,
 * podľa toho či boli uvedené pri spustení, s výnimkou parametru --help.
 * @param $options array zoznam parametrov a ich hodnôt (výsledok getopts())
 * @return array zoznam obsahujúci upravené položky pre všetky parametre
 */
function setDefaultParams($options) {
    # --directory
    if (!isset($options["directory"])) {
        $options["directory"] = getcwd();
    }

    # --parse-script
    if (!isset($options["parse-script"])) {
        $options["parse-script"] = getcwd() . DIRECTORY_SEPARATOR . "parse.php";
    }

    # --int-script
    if (!isset($options["int-script"])) {
        $options["int-script"] = getcwd() . DIRECTORY_SEPARATOR . "interpret.py";
    }

    # --jexamxml
    if (!isset($options["jexamxml"])) {
        $options["jexamxml"] = "/pub/courses/ipp/jexamxml/jexamxml.jar";
    }

    # --jexamcfg
    if (!isset($options["jexamcfg"])) {
        $options["jexamcfg"] = "/pub/courses/ipp/jexamxml/options";
    }

    # --recursive
    if (isset($options["recursive"])) {
        $options["recursive"] = true;
    } else {
        $options["recursive"] = false;
    }

    # --parse-only
    if (isset($options["parse-only"])) {
        $options["parse-only"] = true;
    } else {
        $options["parse-only"] = false;
    }

    # --int-only
    if (isset($options["int-only"])) {
        $options["int-only"] = true;
    } else {
        $options["int-only"] = false;
    }

    return $options;
}

?>