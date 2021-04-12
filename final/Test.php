<?php
/**
 * Súbor obsahuje definícu triedy Test.
 *
 * @author Simon Košina, xkosin09
 */

include_once "errors.php";

/**
 * Trieda Test. Predstavuje 1 test (1 testovací súbor).
 */
class Test
{
    protected $testFile;
    protected $parse_script;
    protected $int_script;
    protected $expected_rc;
    protected $expected_out;
    protected $table;

    /**
     * Test konštruktor.
     * @param $fileName string cesta k .src súboru
     * @param $parse_script string cesta k skriptu pre analýzu
     * @param $int_script string cesta k skriptu pre interpretáciu
     * @param $table Table tabulka pre zapísanie výsledku
     */
    public function __construct($testFile, $parse_script, $int_script, $table) {
        $this->parse_script = $parse_script;
        $this->int_script = $int_script;
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
    public function run()
    {
        $this->setup();

        # meno pre vystupny .xml subor
        $out_xml = "test_out.xml";
        $out_int = "test_int.out";

        while (file_exists($out_xml)) {
            $out_xml = "_" . $out_xml;
        }

        while (file_exists($out_int)) {
            $out_int = "_" . $out_int;
        }

        $cmd = "php7.4 " . $this->parse_script . " < " . $this->testFile . ".src" . " > " . $out_xml;

        $rc = 0;
        $out = null;
        exec($cmd, $out, $rc);

        # vysledok
        $success = true;
        $out_str = "";

        if ($rc == 0) { # analyza ok
            $cmd = "python3.8 " . $this->int_script . " --source=" . realpath($out_xml);
            $cmd = $cmd . " --input=" . realpath($this->testFile . ".in")." >".$out_int." 2>/dev/null";

            $rc = 0;
            $out = array();
            exec($cmd, $out, $rc);

            # ulozenie vystupu
            try {
                $out_str = file_get_contents(realpath($out_int));
            } catch (Exception $e) {
                fprintf(STDERR, $e->getMessage());
                exit(ERR_INTERNAL);
            }

            if ($rc != $this->expected_rc) {
                $success = false;
            }

            # rozlisny vystup
            if ($this->expected_rc == 0) {
                $rc2 = 0;
                $out2 = null;
                $cmd = "diff ".realpath($out_int)." ".realpath($this->testFile.".out");

                exec($cmd, $out2,$rc2);

                # neuspesny diff
                if ($rc2 != 0) {
                    $success = false;
                }
            }
        } else { # chyba pri analyze
            if ($rc != $this->expected_rc) {
                $success = false;
            }
        }

        unlink($out_xml);
        unlink($out_int);

        # pridanie vysledku do tabulky
        $this->table->addTest(basename($this->testFile), $this->expected_rc, $rc, $this->expected_out, $out_str, $success);

        return $success;
    }

    /**
     * Metoda získa očakávaný návratový kód z príslušného súboru.
     * V prípade neexistujúceho .rc súboru je vygenerovaný nový, ktorý obsahuje hodnotu 0.
     */
    protected function loadRC() {
        $name = $this->testFile.".rc";

        try {
  