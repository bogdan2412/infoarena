// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 1000 --memory-limit 10000
// JRUN_RES = FAIL: time [0-9]+ms memory [0-9]{5,}kb: Memory limit exceeded.

#include <sys/types.h>
#include <unistd.h>
#include <string.h>
#include <stdlib.h>
#include <stdio.h>

#define MULT 100000

int main(void)
{
    char* mem[MULT];
    int i;
    for (i = 0; i < MULT; ++i) {
        mem[i] = (char*)malloc(MULT);
    }
    for (i = 0; i < MULT; ++i) {
        mem[rand() % MULT][rand() % MULT] = mem[rand() % MULT][rand() % MULT] + 1000;
    }
    return 0;
}
