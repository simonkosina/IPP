
import sys

def error(msg, exit_code):
    """
    Zobrazenie správy na stderr a ukončenie s odpoovedajúcim návratovým kódom.
    """
    print(msg, file = sys.stderr)
    sys.exit(exit_code)


PARAM = 10
INPUT_FILE = 11
XML_FORMAT = 31
XML_STRUCT = 32
DOUBLE_DEF = 52
UNDEF_VAR = 54
NO_FRAME = 55
