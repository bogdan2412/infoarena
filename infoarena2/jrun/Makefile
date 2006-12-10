# Makefile, for the lazy among you.
all: jrun

.PHONY: all test clean

force:

test: jrun force
	./run-tests.php

clean: force
	rm -rf jrun jrun.o options.o

jrun: jrun.c jrun.h options.c names.c
	gcc -Wall -O2 jrun.c -c -o jrun.o
	gcc -Wall -O2 options.c -c -o options.o
	gcc -Wall -O2 names.c -c -o names.o
	gcc -o jrun jrun.o options.o names.o
	
