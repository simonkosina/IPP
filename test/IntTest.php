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

        $out_int = "test_int.out";

        while (file_exists($out_int)) {
            $out_int = "_" . $out_int;
        }

        $cmd = "python3.8 ".$this->int_script." --source=".realpath($this->testFile.".src");
        $cmd = $cmd." --input=".realpath($this->testFile.".in")." >".$out_int." 2>/dev/null";

        $rc = 0;
        $out = null;
        exec($cmd, $out, $rc);

        # vysledok
        $success = true;

        # rozlisne navratove kody
        if ($rc != $this->expected_rc) {
            $success = false;
        }

        # ulozenie vystupu
        $out_str = "";

        try {
            $out_str = file_get_contents(realpath($out_int));
        } catch (Exception $e) {
            fprintf(STDERR, $e->getMessage());
            exit(ERR_INTERNAL);
        }


        # rozlisny vystup
	    if ($this->expected_rc == 0) {
	        $rc2 = 0;
            $out2 = null;
            $cmd = "diff ".$out_int." ".realpath($this->testFile.".out");

            exec($cmd, $out2, $rc2);

            # neuspesny diff
            if ($rc2 != 0) {
                $success = false;
            }
	    }

        # pridanie vysledku do tabulky
        $this->table->addTest(basename($this->testFile), $this->expected_rc, $rc, $this->expected_out, $out_str, $success);

	    unlink($out_int);
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
