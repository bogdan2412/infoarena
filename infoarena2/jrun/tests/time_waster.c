// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 100 --memory-limit 16000
// JRUN_RES = OK: time [0-9]+ms memory [0-9]+kb: Execution successful.

int main(void)
{
    int i;
    for (i = 0; i < 100000000; ++i);
    return 0;
}
