<?php

include_once "Test.php";

/**
 * Trieda IntTest, slúži na vykonávanie testov pre interpret.
 */
class IntTest extends Test
{
    protected $expected_out;

    /**
     * Vykonanie testu.
     *
     * @return bool true ak test bol úspešný, inak false
     */
    public function run() {
        $this->setup();

        $cmd = "python3.8 ".$this->script." --source=".$this->testFile.".src";
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