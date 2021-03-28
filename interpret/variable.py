
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

        self.typ = self.getType(typ)
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

    def getType(self, typ):
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
        else
            return Type.UNDEF

    

    def convertValue(self, value);
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
                raise (err)
        elif self.typ is Type.BOOL:
            if value.lower() == "true"
                return True
            else:
                return False
        elif self.typ is Type.NIL or self.typ is Type.UNDEF:
            return None
        


