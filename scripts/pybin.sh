#!/usr/bin/env bash

# This tries to compile a Python script into a self-contained binary.
# It uses the freeze.py tool in the standard Python distribution.
# Dependencies are statically linked, with the exception of glibc.
# Compilation will exclude most of the "batteries included" in the
# Python distribution, as they are not appropriate for infoarena.
#
# Usage:
#   pybin.sh <python-dist-dir> <temp-dir> <python-script> <output-binary>
#   (Note: It will fill your temp dir with tons of files!)
#
# Example:
#   ./pybin.sh ~/Desktop/Python-2.6.1 /tmp/pybin myscript.py mybinary

if [ $# -ne 4 ]
then
    echo -n "Usage: "
    echo "pybin.sh <python-dist-dir> <temp-dir> <python-script> <output-binary>"
    exit 1
fi

DIR_PY_DIST=$1
DIR_TMP=$2
PY_SRC=$3
PY_BINARY=$4
PY_TMP_BINARY=$DIR_TMP/`basename $PY_SRC | cut -f1 -d"."`

echo Freezing python module...
FREEZE="$DIR_PY_DIST/python $DIR_PY_DIST/Tools/freeze/freeze.py"
$FREEZE -q -o $DIR_TMP \
        -X BaseHTTPServer -X distutils -X difflib -X EasyDialogs -X email \
        -X email.utils -X FixTk -X ftplib -X getopt -X gettext \
        -X httplib -X MacOS -X mimetools -X msvcrt -X ntpath \
        -X pdb -X pkgutil -X pydoc -X readline -X riscos -X shlex -X socket \
        -X SocketServer -X sre -X sre_compile -X sre_constants -X sre_parse \
        -X ssl -X subprocess -X textwrap -X threading -X Tkinter \
        -X unittest -X urllib -X uu -X _warnings -X webbrowser $PY_SRC

echo Compiling python binary...
# Change Makefile, add static link flag.
mv $DIR_TMP/Makefile $DIR_TMP/Makefile.dynamic
sed $DIR_TMP/Makefile.dynamic -e 's/^LDFLAGS=\(.*\)/LDFLAGS=\1 --static/' > $DIR_TMP/Makefile
make -C $DIR_TMP 1>/dev/null
cp -p $PY_TMP_BINARY $PY_BINARY 2>/dev/null
echo Done

