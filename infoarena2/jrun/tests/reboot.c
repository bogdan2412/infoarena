// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 100 --memory-limit 16000
// JRUN_RES = FAIL: time [0-9]+ms memory [0-9]+kb: Blocked system call: [[:alnum:]]+.

#include <unistd.h>
#include <sys/reboot.h>

int main(void)
{
    reboot(RB_AUTOBOOT);
    return 0;
}
