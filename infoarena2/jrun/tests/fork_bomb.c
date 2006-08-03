// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 1000 --memory-limit 16000
// JRUN_RES = FAIL: time [0-9]+ms memory [0-9]+kb: Blocked system call: [[:alnum:]]+.

#include <sys/types.h>
#include <unistd.h>

int main(void)
{
    while(1) {
        fork();
    }
    return 0;
}
