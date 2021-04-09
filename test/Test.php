<?php

include_once "errors.php";

/**
 * Trieda Test. Predstavuje 1 test (1 testovací súbor).
 */
class Test
{
    protected $testFile;
    protected $intScript;
    protected $parseScript;
    protected $expected_rc;
    protected $expected_out;

    /**
     * Test konštruktor.
     * @param $fileName string cesta k .src súboru
     * @param $intScript string cesta k skriptu pre interpretáciu
     * @param $parseScript string cesta k skriptu pre analýzu
     */
    public function __construct($testFile, $intScript, $parseScript) {
        $this->intScript = $intScript;
        $this->parseScript = $parseScript;
        $this->testFile = substr($testFile, 0, -4);
        $this->expected_rc = 0;
        $this->expected_out = array();
    }

    /**
     * Metoda získa očakávaný návratový kód z príslušného súboru.
     * V prípade neexistujúceho .rc súboru je vygenerovaný nový, ktorý obsahuje hodnotu 0.
     */
    protected function loadRC() {
        $name = $this->testFile.".rc";

        try {
            if (file_exists($name)) {
                $file = fopen($name, "r");
                $this->expected_rc = fgets($file);
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
                $file = fopen($name, "r");

                while (($line = fgets($file)) !== false) {
                    array_push($this->expected_out, rtrim($line, "\n"));
                }
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

    protected function checkScript($name) {
        if (!file_exists($name)) {
            fprintf(STDERR, "Súbor neexistuje: %s\n", $name);
            exit(ERR_FILE_MISSING);
        }
    }

    /**
     * Príprava pred spustením testu.
     */
    protected function setup() {
        $this->loadRC();
        $this->loadOut();
        $this->checkInput();
    }

}

?>