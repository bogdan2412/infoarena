// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 100 --memory-limit 16000
// JRUN_RES = FAIL: time [0-9]+ms memory 1[0-9]{3}kb: Time limit exceeded.

#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#define MULT (1 << 18)

int main(void)
{
    int i;
    static int x[MULT];
    memset(x, 0, sizeof(x));
    x[0] = x[1] = 1;
    while (1) {
        for (i = 2; i < MULT; ++i) {
            x[i] = x[i - 1] + x[i - 2];
        }
    }
    return 0;
}
