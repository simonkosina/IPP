<?php


class Stats
{
    // Pocitadla
    private $loc = 0;
    private $comments = 0;
    private $fwjumps = 0;
    private $backjumps = 0;

    // Zoznamy
    private $labels = array();
    private $undefJumps = array();

    // Informacie o vystupnych suboroch
    private $outputFiles = array();

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

    public function addJump($label) {
        // Navestie uz je definovane -> skok dozadu
        if (in_array($label, $this->labels, true) === true) {
            $this->backjumps++;
        } else { // Navestie nie je def. -> skok dopredu alebo chyba
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
        return count($this->undefJumps);
    }

    public function getJumps() {
        return ($this->getBackJumps() + $this->getFwJumps() + $this->getBadJumps());
    }

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

    public function addFile($file) {
        global $err;

        // Nazov suboru treba vzdy previest na absolutnu cestu,
        // kvoli odhaleniu totoznych suborov zadanych inym sposobom.

        $matches = array();
        $matchCnt= 0;

        // Vyber spravneho vyrazu
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

        // Kontrola, ci dany subor uz bol pouzity
        if (!isset($this->outputFiles[$filePath])) {
            $this->outputFiles[$filePath] = array();
        } else {
            fprintf(STDERR, "Zapisovanie viacerých skupín štatistík do 1 súboru.\n");
            exit($err["outputFiles"]);
        }
    }

    public function addParams2File($file, $param) {
        if (isset($this->outputFiles[$file])) {
            array_push($this->outputFiles[$file], $param);
        }
    }
    public function getOutputFiles() {
        return $this->outputFiles;
    }

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
}

?>