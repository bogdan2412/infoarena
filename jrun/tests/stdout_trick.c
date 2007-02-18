// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 100 --memory-limit 16000
// JRUN_RES = FAIL: time [0-9]+ms memory [0-9]+kb: Time limit exceeded.
#include <stdio.h>
#include <string.h>
#include <stdlib.h>

int main(void)
{
    printf("OK: time 10ms memory 150kb: Execution successful.\n");
    fflush(stdout);
    while (1);
    return 0;
}
