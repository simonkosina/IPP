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
        $this->expected_out = "";
        $this->table = $table;
    }


    /**
     * Vykonanie testu.
     *
     * @return bool true ak test bol úspešný, inak false
     */
    public function run() {
        $this->setup();

        $cmd = "python3.8 ".$this->intScript." --source=".$this->testFile.".src";
        $cmd = $cmd." --input=".$this->testFile.".in 2>/dev/null";

        $rc = "0";
        $out = array();
        exec($cmd, $out, $rc);

        # vysledok
        $success = true;

        # rozlisne navratove kody
        if ($rc != $this->expected_rc) {
            $success = false;
        }

        # rozlisny vystup
        $out_str = implode("\n", $out);
        if (strlen($out_str) != strlen($this->expected_out)) {
            $success = false;
        }

        if ($out_str != $this->expected_out) {
            $success = false;
        }

        # pridanie vysledku do tabulky
        $this->table->addTest(basename($this->testFile), $this->expected_rc, $rc, $this->expected_out, $out_str, $success);

        return $success;
    }
}

?>