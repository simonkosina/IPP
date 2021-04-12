<?php
/**
 * Súbor obsahuje definícu triedy Table.
 *
 * @author Simon Košina, xkosin09
 */

class Table
{
    private $dir;
    private $count_total;
    private $count_success;
    private $doc; # DOMDocument
    private $table; # DOMElement repr. tabulku
    private $tests;

    /**
     * Table konštruktor.
     * @param $dir string názov adresára s testami
     * @param $doc DOMDocument objekt reprezentujúci výstup
     */
    public function __construct($dir, $doc) {
        $this->doc = $doc;
        $this->tests = array();
        $this->dir = $dir;
        $this->count_success = 0;
        $this->count_total = 0;
        $this->createTable();
    }

    /**
     * Získanie riadku pre daný adresár do tabulky adresárov.
     * @return DOMElement riadok tabulky, <tr> element
     */
    public function getSummaryRow() {
        $row = $this->doc->createElement("tr");

        # odtien riadku podla uspesnosti
        $css_folder_class = "td ";

        if ($this->count_total == 0) {
            fprintf(STDERR, "Delenie nulou vo funkcii getSummaryRow() v triede Table.");
            exit(ERR_INTERNAL);
        }

        $success_rate = $this->count_success/$this->count_total;

        if ($success_rate == 1) {
            $css_folder_class = $css_folder_class."folder1";
        } elseif ($success_rate >= 0.66) {
            $css_folder_class = $css_folder_class."folder2";
        } elseif ($success_rate >= 0.33) {
            $css_folder_class = $css_folder_class."folder3";
        } else {
            $css_folder_class = $css_folder_class."folder4";
        }

        $row->setAttribute("class", $css_folder_class);

        # odkaz na vypis testov pre dany adresar
        $row->setAttribute("onclick", "showTable(`".$this->dir."`)");

        # hodnoty stlpcov
        $td = $row->appendChild($this->doc->createElement("td"));
        $td->nodeValue = $this->dir;

        $td = $row->appendChild($this->doc->createElement("td"));
        $td->nodeValue = $this->count_success."/".$this->count_total;

        return $row;
    }

    /**
     * Vygeneruje HTML element reprezentujúci nadpis tabulky. ID sekcie je názov adresára.
     * @return DOMElement nadpis
     */
    public function getTitle() {
        # text
        $title = $this->doc->createElement("div");
        $title->setAttribute("class", "text");
        $title->setAttribute("id", $this->dir);

        # nazov adresara
        $p = $title->appendChild($this->doc->createElement("p"));
        $b = $p->appendChild($this->doc->createElement("strong"));
        $b->nodeValue = "adresár: ";

        # uspesnost adresara
        $p->appendChild($this->doc->createTextNode($this->dir."@emsp;@emsp;"));
        $b = $p->appendChild($this->doc->createElement("strong"));
        $b->nodeValue = "úspešnosť: ";
        $p = $p->appendChild($this->doc->createTextNode($this->count_success."/".$this->count_total));

        return $title;
    }

    /**
     * Vytvorí prázdnu tabulku a uloží ju do $table.
     */
    private function createTable() {
        # tabulka
        $this->table = $this->doc->createElement("table");
        $this->table->setAttribute("class", "table");

        # zahlavie
        $tr = $this->table->appendChild($this->doc->createElement("tr"));

        # subor
        $th = $tr->appendChild($this->doc->createElement("th"));
        $th->nodeValue = "súbor";

        # vysledok
        $th = $tr->appendChild($this->doc->createElement("th"));
        $th->nodeValue = "výsledok";
    }

    /**
     * Vytvorí výslednú tabulku.
     * @return DOMElement tabulka
     */
    public function getTable() {
        ksort($this->tests);

        # pre kazdy vykonany test vygeneruje riadok v tabulke
        foreach ($this->tests as $name => $test) {

            # novy riadok
            $tr = $this->table->appendChild($this->doc->createElement("tr"));
            $tr->setAttribute("class", $test["success"] ? "success td" : " failure td");

            # vazba s funkciou pre otvorenie okna
            $func_call = sprintf("showTest(`%s`,`%s`,`%s`,`%s`,`%s`)", $this->dir.DIRECTORY_SEPARATOR.$name,
                                $test["exp_rc"], $test["act_rc"], $test["exp_out"], $test["act_out"]);
            $tr->setAttribute("onclick", $func_call);

            # nazov suboru
            $td = $tr->appendChild($this->doc->createElement("td"));
            $td->nodeValue = $name;

            # vysledok
            $td = $tr->appendChild($this->doc->createElement("td"));
            $td->nodeValue = $test["success"] ? "úspešný" : "neúspešný";
        }

        return $this->table;
    }

    /**
     * Vytvorí v zozname $this záznam o vykonanom teste.
     * @param $fileName string meno súboru
     * @param $exp_rc string očakávaný návratový kód
     * @param $act_rc string získaný návratový kód
     * @param $exp_out string očakávaný výstup
     * @param $act_out string získaný výstup
     * @param $success bool true ak test bol úspešný, inak false
     */
    public function addTest($fileName, $exp_rc, $act_rc, $exp_out, $act_out, $success) {
        $this->tests[$fileName] = [
            "exp_rc" => $exp_rc,
            "act_rc" => $act_rc,
            "exp_out" => $exp_out,
            "act_out" => $act_out,
            "success" => $success
        ];

        $this->count_total++;

        if ($success) {
            $this->count_success++;
        }
    }

}

?>