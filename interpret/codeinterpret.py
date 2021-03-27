
import errors

class CodeInterpret(object):
    """
    Trieda vykonávajúca interpretáciu kódu.

    Instančné atribúty:
        instructions (dict): order => (kód, [argumenty])
        label_dict (dict): návestie => index v instructions
        gf (frame): globálny rámec
        tf (frame): dočasný rámec, pôvodne None
        lf_stack (list): zásobník lokálnych rámcov
        current_instr (str): meno aktuálne spracovávanej inštrukcie
        current_args (dict): argumenty aktuálne spracovávanej inštrukcie, pozícia => argument
        counter (list): čítač inštrukcií

    Metody:
        - Obsahuje metodu pre každú inštrukciu IPPcode21, ktoré modifikujú stav
          objektu.

    """

    def __init__(self):
        """
        Konštruktor.
        """
        self.instructions = dict()
        self.label_dict = dict()
        self.gf = Frame()
        self.tf = None
        self.lf_stack = list()
        self.current_instr = ""
        self.current_args = dict()
        self.counter = 0

    def newInstruction(self, name):
        """
        Začne spracovávať novú inštrukciu. Vymazanie obsahu zoznamu current_args a prepísanie mena v current_instr.

        Parametre:
            name (str): názov inštrukcie (operačný kód)
        """

        self.current_instr = name
        self.current_args = dict()

    def addArgument(self, value, num):
        """
        Pridanie value na poziciu index num - 1 zoznamu current_args. Predpokladá spravný hodnotu a nevykonáva kontrolu.

        Parametre:
            value (tuple): hodnota argumentu v tvare (typ, hodnota)
            num (int): číslo argumentu
        """

        self.current_args[num] = value

    def finishInstruction(self, order):
        """
        Pridanie aktuálne spracovávanej inštrukcie na pozíciu order - 1 v zozname instructions.

        Parametre:
            order (int): poradie inštrukcie
        """

        args = [self.current_args[key] for key in sorted(self.current_args.keys())]

        if self.current_instr == "LABEL":
            self.addLabel(*args, order)
        
        if order in self.instructions.keys():
            errors.error(f"Opakované zadanie inštrukcie s číslom {order}.", errors.XML_STRUCT)

        self.instructions[order] = (self.current_instr.lower(), args)
        self.current_args = dict()
    
    def run(self):
        """
        Prechádza zoznamom inštrukcií instructions a volá odpovedajúce metody s danými parametrami
        """
        
        num_instr = len(self.instructions)
        keys_sorted = sorted(self.instructions.keys())


        for index, instr in self.instructions.items():
            print(index, instr)

        print("------------------------------")

        while self.counter < num_instr:
            instruction = (self.instructions[keys_sorted[self.counter]])
            print(self.counter, instruction[0], instruction[1])
            getattr(self, instruction[0])(*instruction[1])

            self.counter += 1
            
            print(self.gf.vars)
            print(self.label_dict)
            print("------------------------------")

    def addLabel(self, name, line):
        """
        Pridanie návestia do labels_dict. Bude vykonaný skok na pozíciu
        v instructions, kde sa nachádza návestie. V záp

        Parametre:
            name (tuple): názov návestia (label, meno)
            line (int): číslo riadku (order - 1)
        """

        if name[1] in self.label_dict:
            errors.error(f"Opakovaná definícia návestia '{name[1]}'.", errors.SEMANTIC)

        self.label_dict[name[1]] = line - 1

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

    def parseVariable(self, var):
        """
        Zistí typ a hodnotu premennej. Ak sa jedná o konštantu vráti pôvodnú hodnotu var.

        Parametre:
            var (tuple): meno premennej (typ, hodnota)

        Výstup:
            tuple: (typ, hodnota)
        """

        if var[0] == "var":
            name, frame = self.parseName(var[1])
            var_type = frame.getType(name)
            var_value = frame.getValue(name)
            return (var_type, var_value)
        else:
            return var

    def defvar(self, var):
        """
        Vytvorenie novej premennej v danom rámci.
        
        Parametre:
            var (tuple): názov premennej vo formáte (typ, ramec@meno)
        """
        
        name, frame = self.parseName(var[1])
       
        frame.defVariable(name)

    def move(self, var, symb):
        """
        Uloženie hodnoty symb do premennej var

        Parametre:
            var (string): názov premennej vo formáte (typ, ramec@meno)
            symb (tuple): hodnota vo formáte (typ, hodnota)
        """
        name, frame = self.parseName(var[1])
        
        if symb[0] == "string":
            frame.setValue(name, symb[1])
        elif symb[0] == "bool":
            frame.setValue(name, True if symb[1] == "true" else "false")
        elif symb[0] == "int":
            frame.setValue(name, int(symb[1]))

    def label(self, label):
        """
        Nič sa nevykoná. Návestia sú zaznamenané už pri náčítaní kódu.
        Metóda bola implementovaná kvôli konzistencii spôsobu vykonávania inštrukcií.

        Parametre:
            label (tuple): meno návestia (label, meno)
        """
        
        pass

    def jump(self, label):
        """
        Bude vykonaný skok na pozíciu v instructions, kde sa nachádza návestie label..

        Parametre:
            label (tuple): názov návestia (label, meno)
        """

        if label[1] not in self.label_dict:
            errors.error(f"Skok na nedefinované návestie '{label[1]}'.", errors.SEMANTIC)

        self.counter = self.label_dict[label[1]] 

    def jumpifeq(self, label, symb1, symb2):
        """
        Inštrukcia podmieneného skoku.

        Parametre:
            label (tuple): názov návestia (label, meno)
            symb1 (tuple): argument (typ, hodnota)
            symb2 (tuple): argument (typ, hodnota)
        """
        
        type1, val1 = self.parseVariable(symb1)
        type2, val2 = self.parseVariable(symb2)

        if type1 == "nil" or type2 == "nil":
            self.jump(label)
        elif type1 != type2:
            errors.error(f"Nekompatibilné typy operandov v inštrukcii 'JUMPIFNEQ'.", errors.OP_TYPE)

        if val1 == val2:
            self.jump(label)
    
    def jumpifneq(self, label, symb1, symb2):
        """
        Inštrukcia podmieneného skoku.

        Parametre:
            label (tuple): názov návestia (label, meno)
            symb1 (tuple): argument (typ, hodnota)
            symb2 (tuple): argument (typ, hodnota)
        """
        
        type1, val1 = self.parseVariable(symb1)
        type2, val2 = self.parseVariable(symb2)

        if type1 == "nil" or type2 == "nil":
            self.jump(label)
        elif type1 != type2:
            errors.error(f"Nekompatibilné typy operandov v inštrukcii 'JUMPIFNEQ'.", errors.OP_TYPE)

        if val1 != val2:
            self.jump(label)

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
        
        if self.isDef(name):
            errors.error(f"Opakovaná definícia premennej '{name}'.", errors.SEMANTIC)

        self.vars[name] = None

    def setValue(self, name, value):
        """
        Nastavenie hodnoty premennej.

        Parametre:
            name (string): meno premennej
            value (string, int, bool, None): hodnota premennej
        """

        if not self.isDef(name):
            errors.error(f"Nedefinovaná premenná '{name}'.", errors.UNDEF_VAR)

        self.vars[name] = value

    def getValue(self, name):
        """
        Získanie hodnoty premennej.

        Parametre:
            name (string): meno premennej
        
        Výstup:
            (int, string, bool, None): hodnota premennej
        """

        if not self.isDef(name):
            errors.error(f"Nedefinovaná premenná '{name}'.", errors.UNDEF_VAR)

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
        
        var = self.getValue(name)
        res = ""

        if var is None:
            res = "nil"
        elif type(var) is bool:
            res = "bool"
        elif type(var) is int:
            res = "int"
        elif type(var) is str:
            res = "string"

        return res

