<?php

include_once "test_errors.php";

class Test
{
    private $fileName;
    private $expected_rc;
    private $expected_out;

    /**
     * Test konštruktor.
     * @param $fileName string cesta k .src súboru
     */
    public function __construct($fileName) {
        $this->fileName = substr($fileName, 0, -4);
        $this->expected_rc = 0;
        $this->expected_out = array();
    }

    /**
     * Metoda získa očakávaný návratový kód z príslušného súboru.
     * V prípade neexistujúceho .rc súboru je vygenerovaný nový, ktorý obsahuje hodnotu 0.
     */
    public function loadRC() {
        $name = $this->fileName.".rc";

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
    public function loadOut() {
        $name = $this->fileName.".out";

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

        var_dump($this->expected_out);
    }

}

?>