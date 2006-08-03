// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 100 --memory-limit 16000
// JRUN_RES = FAIL: time [0-9]+ms memory [0-9]+kb: Wall time limit exceeded.

#include <unistd.h>

int main(void)
{
    sleep(100);
    return 0;
}
