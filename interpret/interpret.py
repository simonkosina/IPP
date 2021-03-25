import errors
import sys
import argparse

if __name__ == "__main__":
    # Parse arguments
    parser = argparse.ArgumentParser(description = "IPPcode21 interpret")
    parser.add_argument("--source", metavar = "file", type = str, 
       help = "input file containing the XML representation of the code")
    parser.add_argument("--input", metavar = "file", type = str,
       help = "input file containing the source code")
    args = parser.parse_args()

    if not args.input and not args.source:
        print("Chýbajúcí parameter --source alebo --input.", file = sys.stderr)
        sys.exit(errors.PARAM)
    
    src_file = args.source
    in_file = args.input

    if src_file:
        with open(src_file, "r") as f:

    
