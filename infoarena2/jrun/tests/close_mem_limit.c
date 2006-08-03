// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 500 --memory-limit 6000
// JRUN_RES = OK: time [0-9]+ms memory 5[0-9]{3}kb: Execution successful.

#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#define MULT 5 << 20

int main(void)
{
    int i, j;
    char *x;

    x = (char *)malloc(MULT * sizeof(char));
    for (j = 0; j < 10; ++j) {
        x[0] = x[1] = 1;
        for (i = 2; i < MULT; ++i) {
            x[i] = x[i - 1] + x[i - 2];
        }
    }
    return 0;
}
