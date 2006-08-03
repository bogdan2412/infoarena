// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 2000 --memory-limit 16000
// JRUN_RES = FAIL: time [0-9]{3,}ms memory [0-9]+kb: Time limit exceeded.

int main(void)
{
    int i, j;
    for (i = 0; i < 10000000; ++i) {
        for (j = 0; j < 10000000; ++j) {
        }
    }
    return 0;
}
