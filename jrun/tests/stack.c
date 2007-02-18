// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 200 --memory-limit 16000
// JRUN_RES = OK: time [0-9]+ms memory [0-9]{4,}kb: Execution successful.
#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#define SIZE (1 << 18)

int main(void)
{
    int x[SIZE];
    int i;
    x[0] = x[1] = 1;
    for (i = 2; i < SIZE; ++i) {
        x[i] = x[i - 1] + x[i - 2];
    }
    //return 0;
    freopen("test.out", "wt", stdout);
    if (x[3]) {
        printf("capsuni");
    } else {
        printf("castraveti");
    }
    for (i = 2; i < SIZE; ++i) {
        x[i] = x[i - 1] + x[i - 2];
    }
    return 0;
}
