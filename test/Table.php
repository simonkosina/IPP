<?php


class Table
{
    private $dir;
    private $count_total;
    private $count_success;
    private $html;
    private $doc;

    /**
     * Table konštruktor.
     * @param $dir string názov adresára s testami
     * @param $html DOMElement html výstup
     * @param $doc DOMDocument výstupný dokument
     */
    public function __construct($dir, $html, $doc) {
        $this->doc = $doc;
        $this->dir = $dir;
        $this->count_success = 0;
        $this->count_total = 0;
    }

    public function generateTitle() {
        $title = $this->html->appendChild($this->doc->createElement("div"));
        $title->setAttribute("class", "text");
        $p = $title->appendChild($this->doc->createElement("p"));
        $b = $p->appendChild($this->doc->createElement("strong"));
        $b->nodeValue = "adresár: ";
        $p->appendChild($this->doc->createTextNode($this->dir."@emsp;@emsp;"));
        $b = $p->appendChild($this->doc->createElement("strong"));
        $b->nodeValue = "úspešnosť: ";
        $p = $p->appendChild($this->doc->createTextNode($this->count_success."/".$this->count_total));
    }
}

?>