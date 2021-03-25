
import errors
import sys
import xml.etree.ElementTree as ET

class CodeParser(object):
    """
    Parsuje XML reprezentáciu kódu, vykonáva príslušné kontroly,
    operácie predáva na spracovanie instancii triedy CodeInterpret.
    
    Triedne atribúty:
        opcodes (dict): operačný kód => zoznam argumentov 

    Instančné atribúty:
        src_file (string): názov vstupného súboru
        xml_root (Element): koreň XML stromu

    Metody:
        readInput(self): číta vstup a získa koreň XML stromu
    """

    opcodes = {
        # ramce, volanie funkcii
        "MOVE": ["var", "symb"],
        "CREATEFRAME": list(),
        "PUSHFRAME": list(),
        "POPFRAME": list(),
        "DEFVAR": ["var"],
        "CALL": ["label"],
        "RETURN": list(),
        # zasobnik
        "PUSHS": ["symb"],
        "POPS": ["var"],
        # operatore a konverzie
        "ADD": ["var", "symb", "symb"],
        "SUB": ["var", "symb", "symb"],
        "MUL": ["var", "symb", "symb"],
        "IDIV": ["var", "symb", "symb"],
        "LT": ["var", "symb", "symb"],
        "GT": ["var", "symb", "symb"],
        "EQ": ["var", "symb", "symb"],
        "AND": ["var", "symb", "symb"],
        "OR": ["var", "symb", "symb"],
        "NOT": ["var", "symb", "symb"],
        "INT2CHAR": ["var", "symb"],
        "STRI2INT": ["var", "symb", "symb"],
        # vstup, vystup
        "READ": ["var", "type"],
        "WRITE": ["symb"],
        # op. s retazcami
        "CONCAT": ["var", "symb", "symb"],
        "STRLEN": ["var", "symb"],
        "GETCHAR": ["var", "symb", "symb"],
        "SETCHAR": ["var", "symb", "symb"],
        # op. s typmi
        "TYPE": ["var", "symb"],
        # riadenie toku programu
        "LABEL": ["label"],
        "JUMP": ["label"],
        "JUMPIFEQ": ["label", "symb", "symb"],
        "JUMPIFNEQ": ["label", "symb", "symb"],
        "EXIT": ["symb"],
        # ladiace instrukcie
        "DPRINT": ["symb"],
        "BREAK": list()
        }

    def __init__(self, src_file = None):
        """
        Konštruktor.

        Parametre:
            src_file: názov vstupného súboru (defaultne None, číta zo stdin)
        """
        
        self.src_file = src_file
        self.xml_root = None

    def readInput(self):
        """
        Metóda číta vstup. Do self.xml_root uloží koreň XML stromu.
        """

        xml_string = ""

        if not self.src_file:
                xml_string = sys.stdin.read()
        else:
            try:
                with open(self.src_file, "r") as f:
                    xml_string = f.read()
            except IOError as err:
                print("Chyba pri práci so vstupným súborom.", file = sys.stderr)
                sys.exit(errors.INPUT_FILE)

        try:
            self.xml_root = ET.fromstring(xml_string)
        except ET.ParseError as err:
            print(f"Chybný XML formát vstupného súboru.\nRiadok: {err.position[0]}, Stĺpec: {err.position[1]}", file = sys.stderr)
            sys.exit(errors.XML_FORMAT)

    def parse(self):
        """Parsuje vstupné XML. Kontroluje správnu štruktúru XML stromu.
        Korektné inštrukcie predáva self.interpret na spracovanie."""

        if self.xml_root.tag != "program":
            print("Chybný tag koreňového elementu.", file = sys.stderr)
            sys.exit(errors.XML_STRUCT)

        if "language" not in self.xml_root.attrib or len(self.xml_root.keys()) != 1:
            print("Chybný tag koreňového elementu.", file = sys.stderr)
            sys.exit(errors.XML_STRUCT)
        else:
            if self.xml_root.attrib["language"] != "IPPcode21":
                print("Chybný tag koreňového elementu.", file = sys.stderr)
                sys.exit(errors.XML_STRUCT)
            sys.exit(errors.XML_STRUCT)
