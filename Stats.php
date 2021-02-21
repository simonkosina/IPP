<?php


class Stats
{
    // Pocitadla
    public $loc = 0;
    public $comments = 0;
    public $fwjumps = 0;
    public $backjumps = 0;

    // Zoznamy
    public $labels = array();
    public $undefJumps = array();

    // Informacie o vystupnych suboroch
    public $where = [
        "loc" => array(),
        "comments" => array(),
        "jumps" => array(),
        "fwjumps" => array(),
        "backjumps" => array(),
        "badjumps" => array()
    ];

    public function addFile($file, $key) {
        array_push($this->where[$key], $file);
    }

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
}

?>