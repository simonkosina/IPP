import errors
import sys
import argparse
import codeparser

class ArgumentParser(argparse.ArgumentParser):

    def error(self, message):
        """Redefinícia pôvodnej metody, kvôli zmene návratového kódu."""
    
        self.print_usage(sys.stderr)
        args = {"prog": self.prog, "message": message}
        self.exit(errors.PARAM, ("%(prog)s: error %(message)s\n") % args)

if __name__ == "__main__":
    # Parsovanie argumentov
    parser = ArgumentParser(description = "IPPcode21 interpret")
    parser.add_argument("--source", metavar = "file", type = str, 
       help = "file containing the XML representation of the code")
    parser.add_argument("--input", metavar = "file", type = str,
       help = "file containing the inputs for the interpretation")
    args = parser.parse_args()

    if not args.input and not args.source:
        errors.error("Chýbajúcí parameter --source alebo --input.", errors.PARAM)
    
    src_file = args.source
    in_file = args.input

    # Parsovanie kodu
    parser = codeparser.CodeParser(src_file)
    parser.readInput()
    parser.parseCode()
