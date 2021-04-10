<?php

include_once "Test.php";

/**
 * Trieda ParseTest, slúži na vykonávanie testov pre analyzátor.
 */
class ParseTest extends Test
{
    private $jexamxml;
    private $jexamcfg;

    /**
     * ParseTest konštruktor.
     * @param $fileName string cesta k .src súboru
     * @param $script string cesta k skriptu pre interpretáciu
     * @param $table Table tabulka pre zapísanie výsledku
     * @param $jexamxml string cesta k jexamxml.jar súboru
     * @param $jexamcfg string cesta ku konfiguračnému súboru pre nástroj jexamxml
     */
    public function __construct($testFile, $script, $table, $jexamxml, $jexamcfg)
    {
        $this->jexamxml = $jexamxml;
        $this->jexamcfg = $jexamcfg;
        parent::__construct($testFile, $script, $table);
    }

    /**
     * Vykonanie testu.
     *
     * @return bool true ak test bol úspešný, inak false
     */
    public function run() {
        $this->setup();

        # vytvorenie mena pre vystupny subor
        $out_file_name = "test_out.xml";

        while (file_exists($out_file_name)) {
            $out_file_name = "_".$out_file_name;
        }

        $cmd = "php7.4 ".$this->script." < ".$this->testFile.".src"." > ".$out_file_name;

        $rc = 0;
        $out = null;
        exec($cmd, $out,$rc);

        # vysledok
        $success = true;

        # rozlisne navratove kody
        if ($rc != $this->expected_rc) {
            $success = false;
        }


	    # porovnanie vystupneho XML
	    if ($this->expected_rc == 0) {
		    $xml_cmp_rc = 0;
 
		    $cmd = "java -jar ".$this->jexamxml." ".$out_file_name." ".$this->testFile.".out delta.xml ".$this->jexamcfg;
		    exec($cmd, $out, $xml_cmp_rc);
		
	    	if ($xml_cmp_rc != 0) {
		    	$success = false;
		    }
	    }

	    # pridanie vysledku do tabulky
        try {
	        $outfile_str = file_get_contents($out_file_name);
	    } catch (Exception $e) {
            echo $e->getMessage(), "\n";
            exit(ERR_INTERNAL);
	    }

        $this->table->addTest(basename($this->testFile), $this->expected_rc, $rc, $this->expected_out, $outfile_str, $success);

        unlink(realpath($out_file_name));

        return $success;
    }
}

?>
