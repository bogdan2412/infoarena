/*
 *  JRUN header file.
 *  There is a separate tables.h with syscalls etc, but everything you need is
 *  right here.
 */

#ifndef __JRUN_H__
#define __JRUN_H__

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include <sys/time.h>
#include <sys/types.h>
#include <sys/resource.h>
#include <sys/ptrace.h>
#include <sys/syscall.h>
#include <time.h>
#include <unistd.h>
#include <errno.h>
#include <signal.h>
#include <wait.h>

#define JRUN_CHECK_INTERVAL 50
#define JRUN_JIFFIE_DURATION 10

// FIXME: hacks?
#define MAX_SIGNAL 64
#define MAX_SYSCALL 512

// Signal/syscall names
extern const char* signal_name[MAX_SIGNAL];
extern const char* syscall_name[MAX_SYSCALL];

typedef struct {
    int uid;
    int gid;
    char dir[500];
    char prog[500];

    int time_limit;
    int wall_time_limit;
    int memory_limit;

    int nice_val;
    int ptrace;
    int copy_libs;

    int verbose;
    int chroot;

    int min_proc_update_interval;

    // File to redirect program stdout to.
    // Empty is /dev/null
    char stdin_file[500];
    char stdout_file[500];
    char stderr_file[500];

    // 0 or 1 if a syscall is blocked.
    // Blocking means killing the process.
    int syscall_block[MAX_SYSCALL];
} jrun_options;

// Global options struct.
extern jrun_options jopt;

// Parse options, receives params from main.
void jrun_parse_options(int argc, char **argv);

// Get syscall id from name.
// Returns -1 if not found.
int syscall_getid(char* name);

#endif /* __JRUN_H__ */
