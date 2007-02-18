// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 100 --memory-limit 16000
// JRUN_RES = FAIL: time [0-9]+ms memory [0-9]+kb: Blocked system call: chroot.
//
// This is a classic chroot escape
// It attempts to create a file outside of the jail.
// If it fails the file will appear inside the jail.
// Will succeed if there's no setuid
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/stat.h>
#include <sys/types.h>

int main(void)
{
    mkdir("gigi", 0777);
    chroot("gigi");
    chdir("../");
    FILE *f = fopen("chroot escape", "w");
    fprintf(f, "Hello from the jail!\n");
    fclose(f);
    return 0;
}
