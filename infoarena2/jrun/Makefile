# Makefile, for the lazy retards among you.
all: jrun

.PHONY: all test clean

force:

test: jrun force
	./run-tests.php

clean: force
	rm -rf jrun

jrun: jrun.c tables.h
	gcc -Wall -O2 jrun.c -o jrun
	
