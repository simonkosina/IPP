<?php

include_once "errors.php";

/**
 * Trieda Test. Predstavuje 1 test (1 testovací súbor).
 */
abstract class Test
{
    protected $testFile;
    protected $script;
    protected $expected_rc;
    protected $expected_out;
    protected $table;

    /**
     * Test konštruktor.
     * @param $fileName string cesta k .src súboru
     * @param $script string cesta k skriptu pre interpretáciu
     * @param $table Table tabulka pre zapísanie výsledku
     */
    public function __construct($testFile, $script, $table) {
        $this->script = $script;
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
    abstract public function run();

    /**
     * Metoda získa očakávaný návratový kód z príslušného súboru.
     * V prípade neexistujúceho .rc súboru je vygenerovaný nový, ktorý obsahuje hodnotu 0.
     */
    protected function loadRC() {
        $name = $this->testFile.".rc";

        try {
            if (file_exists($name)) {
                $this->expected_rc = file_get_contents($name);
            } else {
                $this->expected_rc = "0";
                $file = fopen($name, "w");
                fwrite($file, $this->expected_rc);
            }
        } catch (Exception $e) {
            echo $e->getMessage(), "\n";
            exit(ERR_FOPEN_OUT);
        } finally {
            if (isset($file) && $file !== false) {
                fclose($file);
            }
        }
    }

    /**
     * Metoda získa očakávaný výstup z príslušného súboru.
     * V prípade neexistujúceho .out súboru je vygenerovaný nový, prázdny súbor.
     */
    protected function loadOut() {
        $name = $this->testFile.".out";

        try {
            if (file_exists($name)) {
                $this->expected_out = file_get_contents($name);
            } else {
                $file = fopen($name, "w");
            }
        } catch (Exception $e) {
            echo $e->getMessage(), "\n";
            exit(ERR_FOPEN_OUT);
        } finally {
            if (isset($file) && $file !== false) {
                fclose($file);
            }
        }
    }

    /**
     * Funkcia skontroluje existenciu .in súboru a v prípade, že neexistuje vytvorí prázdny súbor.
     */
    protected function checkInput() {
        $name = $this->testFile.".in";

        if (!file_exists($name)) {
            try {
                $file = fopen($name, "w");
                fclose($file);
            } catch (Exception $e) {
                echo $e->getMessage(), "\n";
                exit(ERR_FOPEN_OUT);
            }
        }
    }

    protected function checkScript() {
        if (!file_exists($this->script)) {
            fprintf(STDERR, "Súbor neexistuje: %s\n", $this->script);
            exit(ERR_FILE_MISSING);
        }
    }


    /**
     * Príprava pred spustením testu.
     */
    protected function setup() {
        $this->checkScript();
        $this->checkInput();
        $this->loadRC();
        $this->loadOut();
    }
}

?>