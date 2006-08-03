// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 500 --memory-limit 4000
// JRUN_RES = FAIL: time [0-9]+ms memory [45][0-9]{3}kb: Memory limit exceeded.

#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#define MULT (5 << 20)

int main(void)
{
    int i;
    char *x;
    x = (char *)malloc(MULT * sizeof(char));
    x[0] = x[1] = 1;
    while (1) {
        for (i = 2; i < MULT; ++i) {
            x[i] = x[i - 1] + x[i - 2];
        }
    }
    return 0;
}
