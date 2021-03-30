
import errors
from enum import Enum, auto

class Type(Enum):
    """
    Výčtový typ reprezentujúci typy premenných a literálov.

    Hodnoty:
        UNDEF
        STRING
        BOOL
        INT
        NIL
    """

    UNDEF = auto()
    STRING = auto()
    BOOL = auto()
    INT = auto()
    NIL = auto()

class Variable(object):
    """
    Trieda reprezentuje premennu.

    Instančné atribúty:
        typ (Type): typ premennej
        value: hodnota premennej 
    """

    def __init__(self, typ, value):
        """
        Vytvorenie novej premennej.

        Parameter:
            typ (string): reťazec predstavujúci typ
            value (string): reťazec predstavujúci hodnotu
        """

        self.typ = self.convertType(typ)
        self.value = self.convertValue(value)

    @classmethod
    def fromDefinition(cls, typ, value):
        """ 
        Definicia premennej, známy typ a hodnota.
        """
        
        return cls(typ, value)
        
    @classmethod
    def fromDeclaration(cls):
        """
        Deklerácia premennej, neznámy typ a hodnota.
        """

        return cls("undef", None)

    def __str__(self):
        return f"typ: {self.typ.name}, hodnota: {str(self.value)}"

    def __eq__(self, other):
        if isinstance(other, self.__class__):
            if self.equalTypes(other):
                if self.getValue() == other.getValue():
                    return True
        
        return False

    def convertType(self, typ):
        """
        Zistenie typu premennej.

        Parametre:
            typ (string): string popisujúci meno premennej

        Vystup:
            Type: hodnota typu
        """

        if typ == "string":
            return Type.STRING
        elif typ == "bool":
            return Type.BOOL
        elif typ == "int":
            return Type.INT
        elif typ == "nil":
            return Type.NIL
        else:
            return Type.UNDEF

    def convertValue(self, value):
        """
        Pretypuje hodnotu premennej podľa jej typu.

        Parametre:
            value (string): hodnota

        Vystup:
            int, string, bool: pretypovana hodnota
        """

        if self.typ is Type.STRING:
            return value
        elif self.typ is Type.INT:
            try:
                return int(value)
            except ValueError as err:
                errors.error(f"Hodnotu '{value}' nemožno previesť na typ int.", errors.OP_TYPE)
        elif self.typ is Type.BOOL:
            if value == "true":
                return True
            else:
                return False
        elif self.typ is Type.NIL or self.typ is Type.UNDEF:
            return None
        
    def getValue(self):        
        """
        Získa hodnota premennej.

        Výstup:
            int, string, bool, None: hodnota premennej
        """
        
        return self.value

    def setValue(self, typ, value):
        """
        Nastaví hodnotu premennej. Automaticky dochádza aj k zmene typu.

        Parametre:
            typ (string): typ premennej
            value (string): hodnota premennej
        """

        self.typ = self.convertType(typ)
        self.value = self.convertValue(value)

    def getType(self):
        """
        Získa typ premennej.

        Výstup:
            Type: typ premennej
        """
        
        return self.typ

    def isNil(self):
        """
        Zistí či typ premennej je nil.

        Výstup:
            bool: True ak typ je nil, inak False
        """

        return self.typ is Type.NIL

    def isString(self):
        """
        Zistí či typ premennej je string.

        Výstup:
            bool: True ak typ je string, inak False
        """

        return self.typ is Type.STRING

    def isBool(self):
        """
        Zistí či typ premennej je bool.

        Výstup:
            bool: True ak typ je bool, inak False
        """
        
        return self.typ is Type.BOOL

    def isInitialized(self):
        """
        Zistí či hodnota premennej bola inicializovaná.

        Výstup:
            bool: True ak hodnota bola inicializovaná, inak False
        """
        
        return self.typ != Type.UNDEF

    def equalTypes(self, other):
        """
        Porovná typy 2 premenných.

        Parametre:
            other (Variable): premenná
        """
        
        if not self.isInitialized or not other.isInitialized:
            errors.error(f"Pokus čítanie hodnoty neinicializovanej premennej.", errors.MISSING_VALUE)

        return self.getType() is other.getType()
