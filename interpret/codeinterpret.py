
import errors

class CodeInterpret(object):
    """
    Trieda vykonávajúca interpretáciu kódu.

    Instančné atribúty:
        instr_list (list): usporiadaný zoznam inštrukcíí, inštrukcia je n-tica (kód, [argumenty])
        label_dict (dict): návestie => index v instr_list
        gf (frame): globálny rámec
        tf (frame): dočasný rámec, pôvodne None
        lf_stack (list): zásobník lokálnych rámcov
        current_instr (str): meno aktuálne spracovávanej inštrukcie
        current_args (list): zoznam argumentov aktuálne spracovávanej inštrukcie

    Metody:
        - Obsahuje metodu pre každú inštrukciu IPPcode21, ktoré modifikujú stav
          objektu.

    """

    def __init__(self):
        """
        Konštruktor.
        """
        self.instr_list = list()
        self.label_dict = dict()
        self.gf = Frame()
        self.tf = None
        self.lf_stack = list()
        self.current_instr = ""
        self.current_args = list()

    def newInstruction(self, name):
        """
        Začne spracovávať novú inštrukciu. Vymazanie obsahu zoznamu current_args a prepísanie mena v current_instr.

        Parametre:
            name (str): názov inštrukcie (operačný kód)
        """

        self.current_instr = name
        self.current_args = list()

    def addArgument(self, value, num):
        """
        Pridanie value na poziciu index num - 1 zoznamu current_args. Predpokladá spravný hodnotu a nevykonáva kontrolu.

        Parametre:
            value (tuple): hodnota argumentu v tvare (typ, hodnota)
            num (int): číslo argumentu
        """

        self.current_args.insert(num - 1, value)

    def finishInstruction(self, order):
        """
        Pridanie aktuálne spracovávanej inštrukcie na pozíciu order - 1 v zozname instr_list.

        Parametre:
            order (int): poradie inštrukcie
        """

        self.instr_list.insert(order - 1, (self.current_instr, self.current_args))
        self.current_args = list()
    
    def run(self):
        """
        Prechádza zoznamom inštrukcií instr_list a volá odpovedajúce metody s danými parametrami
        """
        
        for index, instruction in enumerate(self.instr_list[:3]):
            if instruction[0] != "LABEL":
                getattr(self, instruction[0].lower())(*instruction[1])
            else:
                getattr(self, instruction[0].lower())(*instruction[1], index)
            
            print(self.gf.vars)
            print(self.label_dict)
            print("--------------------")

    def parseName(self, name):
        """
        Z názvu premennej odvodí rámec a skontroluje dostupnosť.

        Parametre:
            name (string): názov premennej vo formáte ramec@meno
        
        Výstup:
            (Name, Frame): meno premmenej, rámec premennej
        """

        parts = name.partition("@")
        frame_str = parts[0]
        name = parts[-1]
        
        if frame_str == "GF":
            frame = self.gf
        elif frame_str == "TF":
            if self.tf is None:
                errors.error("Dočasný rámec neexistuje.", errors.NO_FRAME)
            
            frame = self.tf
        elif frame_str == "LF":
            if not self.lf_stack:
                errors.error("Lokálny rámec neexistuje.", errors.NO_FRAME)

            frame = self.lf_stack[-1]

        return (name, frame)

    def defvar(self, var):
        """
        Vytvorenie novej premennej v danom rámci.
        
        Parametre:
            var (tuple): názov premennej vo formáte (typ, ramec@meno)
        """
        
        name, frame = self.parseName(var[1])
        
        if frame.isDef(name):
            errors.error(f"Opakovaná definícia premennej '{name}'.", errors.SEMANTIC)

        frame.defVariable(name)

    def move(self, var, symb):
        """
        Uloženie hodnoty symb do premennej var

        Parametre:
            var (string): názov premennej vo formáte (typ, ramec@meno)
            symb (tuple): hodnota vo formáte (typ, hodnota)
        """

        name, frame = self.parseName(var[1])
        
        if not frame.isDef(name):
            errors.error(f"Nedefinovaná premenná '{var[1]}'.", errors.UNDEF_VAR)

        if symb[0] == "string":
            frame.setValue(name, symb[1])
        elif symb[0] == "bool":
            frame.setValue(name, True if symb[1] == "true" else "false")
        elif symb[0] == "int":
            frame.setValue(name, int(symb[1]))

    def label(self, name, line):
        """
        Pridanie návestia do labels_dict.

        Parametre:
            name (tuple): názov návestia (label, meno)
        """

        if name[1] in self.label_dict:
            errors.error(f"Opakovaná definícia návestia '{name[1]}'.", errors.SEMANTIC)

        self.label_dict[name[1]] = line


class Frame(object):
    """
    Objekt reprezentujúci pamäťový rámec.

    Instančné atribúty:
        vars (dict): meno premennej => hodnota

    Metódy:
        defVariable(self, name): definícia premennej
        setValue(self, name, value): nastavenie novej hodnoty premennej
        getVale(self, name): získanie hodnoty premennej
        isDef(self, name): overenie definície premennej
        getType(self, name): získanie typu premennej
    """

    def __init__(self):
        """
        Konštruktor.
        """

        self.vars = dict()

    def defVariable(self, name):
        """
        Definícia premennej.

        Parametre:
            name (string): meno premennej
        """
        
        self.vars[name] = None

    def setValue(self, name, value):
        """
        Nastavenie hodnoty premennej.

        Parametre:
            name (string): meno premennej
            value (string, int, bool, None): hodnota premennej
        """

        self.vars[name] = value

    def getValue(self, name):
        """
        Získanie hodnoty premennej.

        Parametre:
            name (string): meno premennej
        
        Výstup:
            (int, string, bool, None): hodnota premennej
        """

        return self.vars[name]
    
    def isDef(self, name):
        """
        Overí, či daná premenná je definovaná.

        Parametre:
            name (string): meno premennej

        Výstup:
            bool: True ak premenná je definovaná, inak False

        """

        return name in self.vars

    def getType(self, name):
        """
        Zistí typ premennej.

        Parametre:
            name (string): meno premennej

        Výstup:
            string: typ premennej
        """

        # TODO
        return ""

