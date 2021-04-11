<?php

include_once "Test.php";

/**
 * Trieda IntTest, slúži na vykonávanie testov pre interpret.
 */
class IntTest extends Test
{
    /**
     * Test konštruktor.
     * @param $fileName string cesta k .src súboru
     * @param $script string cesta k skriptu pre interpretáciu
     * @param $table Table tabulka pre zapísanie výsledku
     */
    public function __construct($testFile, $script, $table)
    {
        parent::__construct($testFile, "", $script, $table);
    }

    /**
     * Vykonanie testu.
     *
     * @return bool true ak test bol úspešný, inak false
     */
    public function run() {
        $this->setup();

        $cmd = "python3.8 ".$this->int_script." --source=".realpath($this->testFile.".src");
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

    	$out_str = "";

        # rozlisny vystup
	    if ($this->expected_rc == 0) {
	        $out_str = implode("\n", $out);

	        if ($out_str != $this->expected_out) {
	            $success = false;
	        }
	    }

        # pridanie vysledku do tabulky
        $this->table->addTest(basename($this->testFile), $this->expected_rc, $rc, $this->expected_out, $out_str, $success);

        return $success;
    }

    /**
     * Kontrola existencie testovaného skriptu.
     */
    protected function checkScript() {
        if (!file_exists($this->int_script)) {
            fprintf(STDERR, "Súbor neexistuje: %s\n", $this->int_script);
            exit(ERR_FILE_MISSING);
        }
    }
}

?>
