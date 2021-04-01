
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
    FLOAT = auto()
    NIL = auto()

class Variable(object):
    """
    Trieda reprezentuje premennu.

    Instančné atribúty:
        typ (Type): typ premennej
        value: hodnota premennej 
    """

    def __init__(self, typ, value, fromString = True):
        """
        Vytvorenie novej premennej.

        Parameter:
            typ (string): reťazec predstavujúci typ
            value (string): reťazec predstavujúci hodnotu
            fromString (bool): hodnota predávaná ako string
        """

        self.typ = self.convertType(typ)
        self.value = self.convertValue(value, fromString)

    @classmethod
    def fromDefinition(cls, typ, value, fromString = True):
        """ 
        Definicia premennej, známy typ a hodnota.
        """
        
        return cls(typ, value, fromString)
        
    @classmethod
    def fromDeclaration(cls):
        """
        Deklerácia premennej, neznámy typ a hodnota.
        """

        return cls("undef", None)

    def __str__(self):
        return f"{self.typ.name}@{str(self.value)}"

    def __eq__(self, other):
        if isinstance(other, self.__class__):
            if self.getType() is other.getType():
                return self.getValue() == other.getValue()
        
        errors.error(f"Nekompatibilné typy operandov pri porovnávaní na rovnosť.", errors.OP_TYPE)

    def __lt__(self, other):
        if isinstance(other, self.__class__):
            if not self.isNil() and not other.isNil():
                if self.getType() is other.getType():
                    return self.getValue() < other.getValue()
        
        errors.error(f"Nekompatibilné typy operandov v inštrukcii LT.", errors.OP_TYPE)

    def __gt__(self, other):
        if isinstance(other, self.__class__):
            if not self.isNil() and not other.isNil():
                if self.getType() is other.getType():
                    return self.getValue() > other.getValue()
        
        errors.error(f"Nekompatibilné typy operandov v inštrukcii GT.", errors.OP_TYPE)

    def __add__(self, other):
        if (self.isInt() and other.isInt()) or (self.isFloat() and other.ifFloat()):
            result = Variable.fromDefinition("int", self.getValue() + other.getValue())    
        else:
            errors.error(f"Nekompatibilné typy operandov v inštrukcii ADD.", errors.OP_TYPE)

        return result

    def __sub__(self, other):
        if (self.isInt() and other.isInt()) or (self.isFloat() and other.ifFloat()):
            result = Variable.fromDefinition("int", self.getValue() - other.getValue())    
        else:
            errors.error(f"Nekompatibilné typy operandov v inštrukcii SUB.", errors.OP_TYPE)

        return result

    def __mul__(self, other):
        if (self.isInt() and other.isInt()) or (self.isFloat() and other.ifFloat()):
            result = Variable.fromDefinition("int", self.getValue() * other.getValue())    
        else:
            errors.error(f"Nekompatibilné typy operandov v inštrukcii MUL.", errors.OP_TYPE)

        return result

    def __floordiv__(self, other):
        if (self.isInt() and other.isInt()) or (self.isFloat() and other.ifFloat()):
            if other.getValue() == 0:
                errors.error("Pokus o delenie nulou.", errors.BAD_VAL)

            result = Variable.fromDefinition("int", self.getValue() // other.getValue())    
        else:
            errors.error(f"Nekompatibilné typy operandov v inštrukcii IDIV.", errors.OP_TYPE)

        return result

    def __div__(self, other):
        if self.isFloat() and other.ifFloat():
            if other.getValue() == 0:
                errors.error("Pokus o delenie nulou.", errors.BAD_VAL)

            result = Variable.fromDefinition("int", self.getValue() / other.getValue())    
        else:
            errors.error(f"Nekompatibilné typy operandov v inštrukcii DIV.", errors.OP_TYPE)

        return result


    def __and__(self, other):
        """
        Vykoná logický 'and' medzi hodnotami self a other.

        Výstup:
            bool: hodnota self and hodnota other
        """

        if self.isBool() and other.isBool():
            return self.getValue() and other.getValue()
        else:
            errors.error(f"Nekompatibilné typy operandov v inštrukcii AND.", errors.OP_TYPE)
            
    def __or__(self, other):
        """
        Vykoná logický 'or' medzi hodnotami self a other.

        Výstup:
            bool: hodnota self or hodnota other
        """

        if self.isBool() and other.isBool():
            return self.getValue() or other.getValue()
        else:
            errors.error(f"Nekompatibilné typy operandov v inštrukcii AND.", errors.OP_TYPE)
        
    def __invert__(self):
        """
        Vykoná logickú negáciu hodnoty self.

        Výstup:
            bool: not hodnota self
        """
        
        if self.isBool():
            return not self.getValue()
        else:
            errors.error(f"Nekompatibilné typy operandov v inštrukcii AND.", errors.OP_TYPE)
        
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
        elif typ == "float":
            return Type.FLOAT
        else:
            return Type.UNDEF

    def convertValue(self, value, fromString = True):
        """
        Pretypuje hodnotu premennej podľa jej typu.

        Parametre:
            value (string): hodnota

        Vystup:
            int, string, bool: pretypovana hodnota
            fromString (bool): hodnota predávaná ako string
        """

        if not fromString:
            return value

        if self.typ is Type.STRING:
            return value
        elif self.typ is Type.INT:
            try:
                return int(value)
            except ValueError:
                errors.error(f"Hodnotu '{value}' nemožno previesť na typ int.", errors.OP_TYPE)
        elif self.typ is Type.FLOAT:
            try:
                return float.fromhex(value)
            except ValueError:
                errors.error(f"Hodnotu '{value}' nemožno previesť na typ float.", errors.OP_TYPE)
        elif self.typ is Type.BOOL:
            if value == "true" or value is True:
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
        
        if not self.isInitialized():
            errors.error(f"Pokus čítanie hodnoty neinicializovanej premennej.", errors.MISSING_VALUE)
        
        return self.value

    def setValue(self, typ, value, fromString = True):
        """
        Nastaví hodnotu premennej. Automaticky dochádza aj k zmene typu.

        Parametre:
            typ (string): typ premennej
            value (string): hodnota premennej
            fromString (bool): hodnota predávaná ako string
        """

        self.typ = self.convertType(typ)
        self.value = self.convertValue(value, fromString)

    def getType(self):
        """
        Získa typ premennej.

        Výstup:
            Type: typ premennej
        """

        if not self.isInitialized():
            errors.error(f"Pokus čítanie hodnoty neinicializovanej premennej.", errors.MISSING_VALUE)
        
        return self.typ

    def isNil(self):
        """
        Zistí či typ premennej je nil.

        Výstup:
            bool: True ak typ je nil, inak False
        """

        return self.getType() is Type.NIL

    def isString(self):
        """
        Zistí či typ premennej je string.

        Výstup:
            bool: True ak typ je string, inak False
        """

        return self.getType() is Type.STRING

    def isBool(self):
        """
        Zistí či typ premennej je bool.

        Výstup:
            bool: True ak typ je bool, inak False
        """
        
        return self.getType() is Type.BOOL

    def isInt(self):
        """
        Zistí či typ premennej je int.

        Výstup:
            bool: True ak typ je int, inak False
        """
        
        return self.getType() is Type.INT

    def isFloat(self):
        """
        Zistí či typ premennej je float.

        Výstup:
            bool: True ak typ je float, inak False
        """
        
        return self.getType() is Type.FLOAT

    def isInitialized(self):
        """
        Zistí či hodnota premennej bola inicializovaná.

        Výstup:
            bool: True ak hodnota bola inicializovaná, inak False
        """
        
        return self.typ != Type.UNDEF
