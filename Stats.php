<?php

class Stats
{
    private $loc = 0; // pocet prikazov
    private $comments = 0; // pocet comentarov
    private $fwjumps = 0; // pocet skokov do predu
    private $backjumps = 0; // pocet skokov do zadu

    private $labels = array(); // navestia
    private $undefJumps = array(); // chybne skoky alebo skoky do predu

    private $outputFiles = array(); // subory pre vypis statistik

    // $code - vstupny kod transformovany na zoznam prikazov
    public function setLoc($code) {
        $this->loc = count($code)-1;
    }

    public function getLoc() {
        return $this->loc;
    }

    public function incComments() {
        $this->comments++;
    }

    public function getComments() {
        return $this->comments;
    }

    // Zaznamenanie instrukcie skoku
    // Ak sa navestie uz nachadza v zozname $labels, jedna sa o skok do zadu.
    // V opacnom pripade je to bud skok do predu alebo chybne navestie.
    // $label - navestie pouzite v instrukcii skoku
    public function addJump($label) {
        if (in_array($label, $this->labels, true) === true) {
            // Navestie uz je definovane.
            $this->backjumps++;
        } else {
            // Navestie nie je definovane.
            array_push($this->undefJumps, $label);
        }
    }

    public function getFwJumps() {
        return $this->fwjumps;
    }

    public function getBackJumps() {
        return $this->backjumps;
    }

    public function getBadJumps() {
        // Pocet navesti v $undefJumps == Pocet zlych skokov
        return count($this->undefJumps);
    }

    public function getJumps() {
        // jumps = backjumps + fwjumps + badjumps
        return ($this->getBackJumps() + $this->getFwJumps() + $this->getBadJumps());
    }

    // Pridanie navestia do zoznamu $labels
    // Ak dane navestie bolo v zozname $undefJumps,
    // inkrementuje pocitadlo $fwjumps a navestia zo zoznamu odstrani.
    // Nekontroluje duplikatne definicie navestia.
    // $label - navestie pouzite v instrukcii LABEL
    public function addLabel($label) {
        // Ak dane navestia este nebolo definovane
        if (in_array($label, $this->labels, true) === false) {
            array_push($this->labels, $label);

            $res = array_search($label, $this->undefJumps, true);

            // Na dane navestie bol uz vykonany skok
            if ($res !== false) {
                unset($this->undefJumps[$res]);
                $this->fwjumps++;
            }
        }
    }

    public function getLabels() {
        return count($this->labels);
    }

    // Ziska absolutnu cestu k suboru, kvoli kontrole
    // identickych suborov zadanych roznym sposobom.
    // Pre subor vytvori polozku v zozname $outputFiles, do
    // ktorej priradi prazdne pole.
    // $file - nazov suboru (rel., abs., meno)
    public function addFile($file) {
        global $err;

        $filePath = $this->createFilePath($file);

        // Kontrola, ci dany subor uz bol pouzity
        if (!isset($this->outputFiles[$filePath])) {
            $this->outputFiles[$filePath] = array();
        } else {
            fprintf(STDERR, "Zapisovanie viacerých skupín štatistík do 1 súboru.\n");
            exit($err["outputFiles"]);
        }
    }

    // Do zoznamy polozky $file v $outputFiles, prida
    // nazov statistiky pre vypis.
    // $param - statistika, kt. sa bude do suboru vypisovat
    // $file - nazov suboru (rel., abs., meno)
    public function addParams2File($file, $param) {
        $filePath = $this->createFilePath($file);

        if (isset($this->outputFiles[$filePath])) {
            array_push($this->outputFiles[$filePath], $param);
        }
    }

    public function getOutputFiles() {
        return $this->outputFiles;
    }

    // Vrati zoznam obsahujuci hodnoty vsetkych statistik.
    public function getStatsArray() {
        $arr = [
            "--loc" => $this->getLoc(),
            "--comments" => $this->getComments(),
            "--labels" => $this->getLabels(),
            "--jumps" => $this->getJumps(),
            "--fwjumps" => $this->getFwJumps(),
            "--backjumps" => $this->getBackJumps(),
            "--badjumps" => $this->getBadJumps(),
        ];

        return $arr;
    }

    // Do suborov v $outputFiles, vypise hodnoty prislusnych statistik.
    public function printStats() {
        global $err;

        $fileArray = $this->getOutputFiles();
        $statsArray = $this->getStatsArray();

        foreach ($fileArray as $fileName => $stats2write) {
            $file = fopen($fileName, 'w');

            if ($file === false) {
                fprintf(STDERR, "Chyba pri otváraní súboru '%s'.\n", $fileName);
                exit($err["outputFiles"]);
            }

            foreach ($stats2write as $param) {
                fprintf($file, "%d\n", $statsArray[$param]);
            }

            fclose($file);
         }
    }

    // Vrati absolutnu cestu k suboru.
    // $file - nazov suboru (rel., abs., meno)
    private function createFilePath($file) {
        $matches = array();
        $matchCnt= 0;

        // Vyber spravneho reg. vyrazu
        if (DIRECTORY_SEPARATOR == "\\") {
            $pathPattern = "/.*\\\/";
        } else {
            $pathPattern = "/.*\\//";
        }

        $matchCnt = preg_match($pathPattern, $file, $matches); // abs./rel. cesta do adresara
        $fileName = preg_replace($pathPattern, "", $file); // meno suboru

        // Nastavenie abs. cesty k adresaru
        if ($matchCnt == 1) {
            $dirPath = realpath($matches[0]);
        } else {
            $dirPath = realpath('.');
        }

        // Nastavenie abs. cesty k suboru
        $filePath = $dirPath.DIRECTORY_SEPARATOR.$fileName; // absolutna cesta k suboru

        return $filePath;
    }
}

?>