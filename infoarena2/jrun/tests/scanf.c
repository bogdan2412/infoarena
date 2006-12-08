// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 1000 --memory-limit 16000
// JRUN_RES = FAIL: time [0-9]+ms memory [0-9]+kb: Killed by signal 11\(SIGSEGV\).

#include <sys/types.h>
#include <unistd.h>
#include <string.h>
#include <stdlib.h>
#include <stdio.h>

int main(void)
{
    int x;
    while (1) {
        scanf("%d", &x);
        printf("%d", x);
    }
    return 0;
}
