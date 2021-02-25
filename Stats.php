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

        if (!isset($this->outputFiles[$file])) {
            // Vytvorenie novej polozky v $outputFiles
            $this->outputFiles[$file] = array();
        } else {
            fprintf(STDERR, "Zapisovanie viacerých skupín štatistík do 1 súboru.\n");
            exit($err["outputFiles"]);
        }
    }

    public function addFileAndParams($file, $param, $sameFileEnable) {
        global $err;

        if (isset($this->outputFiles[$file])) {
            // Uz existuje

            if ($sameFileEnable) {
                array_push($this->outputFiles[$file], $param);
            } else {
                // Nie je povolene zapisat do uz exist. suboru
                fprintf(STDERR, "Zapisovanie viacerých skupín štatistík do 1 súboru.\n");
                exit($err["outputFiles"]);
            }

        } else {
            // Vytvorenie novej polozky v $outputFiles
            $this->outputFiles[$file] = array();
            array_push($this->outputFiles[$file], $param);
        }
    }

    public function getOutputFiles() {
        return $this->outputFiles;
    }
}

?>