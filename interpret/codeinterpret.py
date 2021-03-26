
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
            value (str): hodnota argumentu
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
        
        for instruction in self.instr_list[:2]:
            getattr(self, instruction[0].lower())(*instruction[1])

    def defvar(self, var):
        pass

    def move(self, var, symb):
        pass

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

    def defVariable(self, name)
        """
        Definícia premennej.

        Parametre:
            name (string): meno premennej
        """

        self.vars[name] = None

    def setValue(self, name, value)
        """
        Nastavenie hodnoty premennej.

        Parametre:
            name (string): meno premennej
            value (string, int, bool, None): hodnota premennej
        """

        self.vars[name] = value

    def getValue(self, name)
        """
        Získanie hodnoty premennej.

        Parametre:
            name (string): meno premennej
        
        Výstup:
            (int, string, bool, None): hodnota premennej
        """

        return self.vars[name]
    
    def isDef(self, name)
        """
        Overí, či daná premenná je definovaná.

        Parametre:
            name (string): meno premennej

        Výstup:
            bool: True ak premenná je definovaná, inak False

        """

        return name in self.vars

    def getType(self, name)
        """
        Zistí typ premennej.

        Parametre:
            name (string): meno premennej

        Výstup:
            string: typ premennej
        """

        # TODO
        return ""

