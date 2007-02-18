// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 100 --memory-limit 16000
// JRUN_RES = OK: time [0-9]+ms memory [0-9]+kb: Execution successful.
#include <stdio.h>
#include <string.h>
#include <stdlib.h>

int main(void)
{
    printf("Hello World!!!\n");
    return 0;
}
