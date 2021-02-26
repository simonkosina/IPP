<?php

// Subor obsahuje definicie regularnych vyrazov
// a pomocnych premmenych pre syntakticku analyzu kodu.

$statsParam = "/(--stats=)(.+)/";

$header = "/^.IPPcode21$/i";

$comment = "/#.*/i";

// alfanum. retazec zac. pismenom alebo spec. znakom
$name = "[a-zA-Z_\-$&%*!?][a-zA-Z_\-$&%*!?0-9]*";

$var = "/^[L,T,G]F@{$name}$/"; // premenna

$nil = "/^(nil)@(nil)$/"; // typ nil

$bool = "/^(bool)@((true)|(false))$/"; // typ bool

// $int = "/^(int)@([+-]?[\d]+)$/"; // typ int
$int = "/^(int)@(.*)$/"; // typ int, bez kontroly literalu

$string = "/^(string)@((?:(?:\\\d\d\d)|(?:[^\\#\s]*))*)$/u"; // typ string

// Regularne vyrazy pre porovnavanie argumentov prikazov
$args = [
    "var" => ["var" => $var],
    "label" => ["label" => "/^{$name}$/"],
    "symb" => ["var" => $var,
        "nil" => $nil,
        "bool" => $bool,
        "int" => $int,
        "string" => $string], // premenna alebo konstanta
    "type" => ["type" => "/(^int$)|(^string$)|(^bool$)/"]
];

// index je nazov instrukcie
// hodnota je pole neterminalov, ktore musia nasledovat
// za danou instrukciou (indexy do $args)
$instructions = [
// ramce, volanie funkcii
    "MOVE" => ["var", "symb"],
    "CREATEFRAME" => array(),
    "PUSHFRAME" => array(),
    "POPFRAME" => array(),
    "DEFVAR" => ["var"],
    "CALL" => ["label"],
    "RETURN" => array(),
// zasobnik
    "PUSHS" => ["symb"],
    "POPS" => ["var"],
// operatore a konverzie
    "ADD" => ["var", "symb", "symb"],
    "SUB" => ["var", "symb", "symb"],
    "MUL" => ["var", "symb", "symb"],
    "IDIV" => ["var", "symb", "symb"],
    "LT" => ["var", "symb", "symb"],
    "GT" => ["var", "symb", "symb"],
    "EQ" => ["var", "symb", "symb"],
    "AND" => ["var", "symb", "symb"],
    "OR" => ["var", "symb", "symb"],
    "NOT" => ["var", "symb", "symb"],
    "INT2CHAR" => ["var", "symb"],
    "STRI2INT" => ["var", "symb", "symb"],
// vstup, vystup
    "READ" => ["var", "type"],
    "WRITE" => ["symb"],
// op. s retazcami
    "CONCAT" => ["var", "symb", "symb"],
    "STRLEN" => ["var", "symb"],
    "GETCHAR" => ["var", "symb", "symb"],
    "SETCHAR" => ["var", "symb", "symb"],
// op. s typmi
    "TYPE" => ["var", "symb"],
// riadenie toku programu
    "LABEL" => ["label"],
    "JUMP" => ["label"],
    "JUMPIFEQ" => ["label", "symb", "symb"],
    "JUMPIFNEQ" => ["label", "symb", "symb"],
    "EXIT" => ["symb"],
// ladiace instrukcie
    "DPRINT" => ["symb"],
    "BREAK" => array()
];

?>