
import codeinterpret
import re
import errors
import sys
import xml.etree.ElementTree as ET

class CodeParser(object):
    """
    Parsuje XML reprezentáciu kódu, vykonáva príslušné kontroly,
    operácie predáva na spracovanie instancii triedy CodeInterpret.
    
    Triedne atribúty:
        opcodes (dict): operačný kód => zoznam nontermov repr. argumenty 
        expand_types (dict): nonterm => množina typov
        value_patterns (dict): typ => reg. výraz pre kontrolu hodnoty

    Instančné atribúty:
        src_file (string): názov vstupného súboru
        xml_root (Element): koreň XML stromu
        interpret (CodeInterpret): objekt vykonávajúci interpretáciu

    Metody:
        readInput(self): číta vstup a získa koreň XML stromu
        parseCode(self): spracovávanie XML stromu
        parseIntruction(self, instruction): spracovávanie XML elementu 'instruction'
        parseArgs(self, arg_el, opcode, order): spracovávanie XML elementu 'argN'
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

    
    expand_types = {
        "var": ("var"),
        "symb": ("var", "nil", "bool", "int", "string"),
        "label": ("label"),
        "type": ("type"),
        }

    value_patterns = {
        "var": r"^[L,T,G]F@[a-zA-Z_\-$&%*!?][a-zA-Z_\-$&%*!?0-9]*$",
        "nil": r"^(nil)$",
        "bool": r"^(true|false)$",
        "int": r"^(int)@(.*)$",
        "string": r"^((?:(?:\\\d\d\d)|(?:[^\\\#\s]*))*)$",
        "label": r"^[a-zA-Z_\-$&%*!?][a-zA-Z_\-$&%*!?0-9]*$",
        "type": r"(^int$)|(^string$)|(^bool$)",
        }

    def __init__(self, src_file = None):
        """
        Konštruktor.

        Parametre:
            src_file (str): názov vstupného súboru (defaultne None, číta zo stdin)
        """
        
        self.src_file = src_file
        self.xml_root = None
        self.interpret = codeinterpret.CodeInterpret()

    def readInput(self):
        """
        Metóda číta vstup. Do self.xml_root uloží koreň XML stromu.
        """

        xml_string = ""

        # Nacitanie vstupu
        if not self.src_file:
                xml_string = sys.stdin.read()
        else:
            try:
                with open(self.src_file, "r") as f:
                    xml_string = f.read()
            except IOError as err:
                errors.error("Chyba pri práci so vstupným súborom.", errors.INPUT_FILE)

        # Vytvorenie XML stromu
        try:
            self.xml_root = ET.fromstring(xml_string)
        except ET.ParseError as err:
            errors.error(f"Chybný XML formát vstupného súboru.\nRiadok: {err.position[0]}, Stĺpec: {err.position[1]}", errors.XML_FORMAT)

    def parseCode(self):
        """
        Parsuje vstupné XML. Kontroluje správnu štruktúru XML stromu.
        Korektné inštrukcie predáva self.interpret na spracovanie.
        """

        # Korenovy element
        if self.xml_root.tag != "program":
            errors.error(f"Chybný tag koreňového elementu.\nOčakávaný: 'program', Uvedený: '{self.xml_root.tag}'", errors.XML_STRUCT)

        attributes = self.xml_root.attrib.keys()

        # Obsahuje iba povolene atributy
        for attrib in attributes:
            if attrib not in ("language", "name", "description"):
                errors.error(f"Chybný atribút elementu {self.xml_root.tag}.\nOčakávaný: {{'language', 'name', 'description'}}, Uvedený: '{attrib}'", errors.XML_STRUCT)

        # Obsahuje povinny atribut language
        if "language" not in attributes:
            errors.error("Chýba povinný atribút 'language' elementu 'program'.", errors.XML_STRUCT)

        # Spravna hodnota atributu 'language'
        if self.xml_root.attrib["language"] != "IPPcode21":
            errors.error("Chybná hodnota atribútu 'language'.\nOčakávaná: IPPcode21, Uvedená: {self.xml_root.attrib['language']}.", errors.XML_STRUCT)
       
        # Kontrola instrukcii
        for el in self.xml_root:
            self.parseInstruction(el)
        
        self.interpret.run()

    def parseInstruction(self, instruction):
        """
        Skontroluje správnosť danej inštrukcie a predá spracovanie objektu self.interpret.

        Parametre:
            instruction (ET.Element): XML element predstavujúci danú inštrukciu
        """
        
        if instruction.tag != "instruction":
            errors.error(f"Chybný tag elementu.\nOčakávaný: 'instruction', Uvedený: '{instruction.tag}'", errors.XML_STRUCT)
        
        attributes = instruction.attrib.keys()
       
        # Kontrola atributov
        for attrib in attributes:
            if attrib not in ("order", "opcode"):
                errors.error(f"Chybný atribút elementu {self.xml_root.tag}.\nOčakávaný: {{'order', 'opcode'}}, Uvedený: '{attrib}'", errors.XML_STRUCT)
         
        if len(attributes) != 2:
            errors.error("Chýbajúci atribút 'opcode' alebo 'order' elementu 'instruction'", errors.XML_STRUCT)

        opcode = instruction.attrib["opcode"]
        order = instruction.attrib["order"]

        # Kontrola atributu order
        if not order.isdigit():
            errors.error(f"Chybná hodnota atribútu 'order' inštrukcie '{opcode}'.", errors.XML_STRUCT)
       
        order = int(order)

        # Kontrola operacneho kodu instrukcie
        if opcode not in self.__class__.opcodes:
            errors.error(f"Chybný operačný kód inštrukcie číslo {order}.", errors.XML_STRUCT)

        self.interpret.newInstruction(opcode)

        # Kontrola argumentov
        arg_numbers = set()

        for arg in instruction:
            arg_num = self.parseArg(arg, opcode, order)
            
            if arg_num in arg_numbers:
                errors.error(f"Opakovane zadaný argument {arg_num} inštrukcie {opcode} číslo {order}", errors.XML_STRUCT)
            
            arg_numbers.add(arg_num)

        if len(arg_numbers) != len(self.__class__.opcodes[opcode]):
            errors.error(f"Chybný počet argumentov inštrukcie '{opcode}' číslo {order}.", errors.XML_STRUCT)
        
        self.interpret.finishInstruction(order)

    def parseArg(self, arg_el, opcode, order):
        """
        Skontroluje správnosť argumentu inštrukcie s operačným kódom opcode.
        
        Výstup:
            int: číslo argumentu
        """
        
        arg_list = self.__class__.opcodes[opcode]
        
        # Kontrola nazvu atributu
        if arg_el.tag[:3] != "arg":
            errors.error(f"Chybný tag elementu.\nOčakávaný: 'arg{{cislo}}', Uvedený: '{arg_el.tag}'\nČíslo inštrukcie: {order}", errors.XML_STRUCT)
        
        if not arg_el.tag[3:].isdigit():
            errors.error(f"Chybný tag elementu.\nOčakávaný: 'arg{{cislo}}', Uvedený: '{arg_el.tag}'\nČíslo inštrukcie: {order}", errors.XML_STRUCT)
        
        arg_num = int(arg_el.tag[3:])
            
        # Kontrola typu argumentu
        if len(arg_el.attrib.keys()) != 1 or "type" not in arg_el.attrib.keys():
            errors.error(f"Chybne uvedené argumenty elementu 'arg'.\nČíslo inštrukcie: {order}", errors.XML_STRUCT)

        if len(arg_list) < arg_num:
            errors.error(f"Chybne očíslovaný argument inštrukcie '{opcode}' číslo {order}.", errors.XML_STRUCT)

        arg_type = arg_el.attrib["type"]

        if arg_type not in self.__class__.expand_types[arg_list[arg_num-1]]:
            errors.error(f"Chybná hodnota atribútu 'type' elementu '{arg_el.tag}'.\nČíslo inštrukcie: {order}", errors.XML_STRUCT)

        # Kontrola hodnoty argumentu
        if arg_el.text is None: 
            arg_el.text = ""
        
        match = re.search(self.__class__.value_patterns[arg_type], arg_el.text)
        
        if match is None:
            errors.error(f"Chybná hodnota argumentu '{arg_type}' inštrukcie '{opcode}'.\nČíslo inštrukcie: {order}", errors.XML_STRUCT)
    
        self.interpret.addArgument((arg_type, match[0]), arg_num)
        
        return arg_num
