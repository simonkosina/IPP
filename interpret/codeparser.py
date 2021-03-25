
import errors
import sys
import xml.etree.ElementTree as ET

class CodeParser(object):
    """
    Parsuje XML reprezentáciu kódu, vykonáva príslušné kontroly,
    operácie predáva na spracovanie instancii triedy CodeInterpret.
    
    Atribúty:
        src_file (string): názov vstupného súboru
        xml_root (Element): koreň XML stromu

    Metody:
        readInput(self): číta vstup a získa koreň XML stromu
    """

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

    
