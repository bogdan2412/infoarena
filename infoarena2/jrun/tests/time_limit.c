// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 2000 --memory-limit 16000
// JRUN_RES = FAIL: time [0-9]{3,}ms memory [0-9]+kb: Time limit exceeded.

#include <stdio.h>

int main(void)
{
    int i, j;
    int a[8];
    for (i = 0; i < 100000000; ++i) {
        for (j = 0; j < 100000000; ++j) {
            a[i & 7] += a[j & 7];
        }
    }
    printf("%d %d", a[0], a[3]);
    return 0;
}
