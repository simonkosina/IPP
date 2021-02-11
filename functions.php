<?php

function loadFile() {
    global $comment;
    $file = array();

    while (($line = fgets(STDIN)) != false) {
        // Odstranenie komentarov
        $noCommLine = preg_replace($comment, "", $line);
        $noCommLine = trim($noCommLine);

        if (empty($noCommLine) == false) {
            $splitLine = preg_split("/ +/i", $noCommLine);
            $splitLine[0] = strtoupper($splitLine[0]); // jednotna velkost kvoli indexovaniu
            array_push($file, $splitLine);
        }
    }

    return $file;
}

?>