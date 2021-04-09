<?php

include_once "Test.php";

/**
 * Trieda IntTest, slúži na vykonávanie testov pre interpret.
 */
class IntTest extends Test
{
    /**
     * IntTest konštruktor.
     * @param $fileName string cesta k .src súboru
     * @param $intScript string cesta k skriptu pre interpretáciu
     * @param $table Table tabulka pre zapísanie výsledku
     */
    public function __construct($testFile, $intScript, $table) {
        $this->checkScript($intScript);
        $this->intScript = $intScript;
        $this->testFile = substr($testFile, 0, -4);
        $this->expected_rc = 0;
        $this->expected_out = array();
        $this->table = $table;
    }


    /**
     * Vykonanie testu.
     */
    public function run() {
        $this->setup();

        $cmd = "python3.8 ".$this->intScript." --source=".$this->testFile.".src";
        $cmd = $cmd." --input=".$this->testFile.".in 2>/dev/null";

        $rc = 0;
        $out = array();
        exec($cmd, $out, $rc);

        # vysledok
        $success = true;

        # rozlisne navratove kody
        if ($rc != $this->expected_rc) {
            $success = false;
        }

        # rozlisny vystup
        if (count($out) != count($this->expected_out)) {
            $success = false;
        }

        for ($i = 0; $i < count($out); $i++) {
            if ($out[$i] != $this->expected_out[$i]) {
                $success = false;
            }
        }

        # pridanie vysledku do tabulky
        $this->table->addTest(basename($this->testFile), $this->expected_rc, $rc, implode("\n", $this->expected_out), implode("\n", $out), $success);
    }
}

?>