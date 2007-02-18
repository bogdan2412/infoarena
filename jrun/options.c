/*
 * This file parses options, see jrun.c for usage.
 * Uses getopt
 */
#include "jrun.h"
#include <getopt.h>

jrun_options jopt;

// Stupid constants used with getopt
#define OPT_UID                         0
#define OPT_GID                         1
#define OPT_DIR                         2
#define OPT_PROG                        3
#define OPT_TIME_LIMIT                  4
#define OPT_WALL_TIME_LIMIT             15
#define OPT_MEMORY_LIMIT                5
#define OPT_NICE                        6
#define OPT_NO_PTRACE                   7
#define OPT_COPY_LIBS                   8
#define OPT_VERBOSE                     9
#define OPT_BLOCK_SYSCALLS              10
#define OPT_BLOCK_SYSCALLS_FILE         11
#define OPT_REDIRECT_STDIN               17
#define OPT_REDIRECT_STDOUT              12
#define OPT_REDIRECT_STDERR              13
#define OPT_CHROOT                      14

void jrun_parse_options(int argc, char *argv[])
{
    struct option long_options[] = {
        {"uid",                 1, 0, OPT_UID},
        {"gid",                 1, 0, OPT_GID},
        {"dir",                 1, 0, OPT_DIR},
        {"prog",                1, 0, OPT_PROG},
        {"time-limit",          1, 0, OPT_TIME_LIMIT},
        {"wall-time-limit",     1, 0, OPT_WALL_TIME_LIMIT},
        {"memory-limit",        1, 0, OPT_MEMORY_LIMIT},
        {"nice",                1, 0, OPT_NICE},
        {"no-ptrace",           0, 0, OPT_NO_PTRACE},
        {"copy-libs",           0, 0, OPT_COPY_LIBS},
        {"verbose",             0, 0, OPT_VERBOSE},
        {"block-syscalls",      1, 0, OPT_BLOCK_SYSCALLS},
        {"block-syscalls-file", 1, 0, OPT_BLOCK_SYSCALLS_FILE},
        {"redirect-stdin",      1, 0, OPT_REDIRECT_STDIN},
        {"redirect-stdout",     1, 0, OPT_REDIRECT_STDOUT},
        {"redirect-stderr",     1, 0, OPT_REDIRECT_STDERR},
        {"chroot",              0, 0, OPT_CHROOT},
        {0, 0, 0, 0},
    };
    int option_index = 0;

    // Initioalize options:
    memset(jopt.syscall_block, 0, sizeof(jopt.syscall_block));
    jopt.uid = -1;
    jopt.gid = -1;

    jopt.prog[0] = 0;
    getcwd(jopt.dir, sizeof(jopt.dir));
   
    jopt.time_limit = 0;
    jopt.wall_time_limit = -1;
    jopt.memory_limit = 0;

    jopt.nice_val = -1000;
    jopt.ptrace = 1;
    jopt.copy_libs = 0;
    jopt.min_proc_update_interval = 5;

    jopt.verbose = 0;
    jopt.chroot = 0;
    jopt.stdout_file[0] = jopt.stderr_file[0] = jopt.stdin_file[0] = 0;

    while (1) {
        int opt = getopt_long(argc, argv, "u:g:d:p:n:t:w:m:v", long_options, &option_index);
        if (opt == -1) {
            break;
        }
        switch (opt) {
            case 'u': case OPT_UID:
                if (sscanf(optarg, "%d", &(jopt.uid)) != 1) {
                    printf("ERROR: --uid needs an int parameter\n");
                    exit(-1);
                }
                break;
            case 'g': case OPT_GID:
                if (sscanf(optarg, "%d", &(jopt.gid)) != 1) {
                    printf("ERROR: --gid needs an int parameter\n");
                    exit(-1);
                }
                break;
            case 'd': case OPT_DIR:
                strcpy(jopt.dir, optarg);
                break;
            case 'p': case OPT_PROG:
                strcpy(jopt.prog, optarg);
                break;
            case 'n': case OPT_NICE:
                if (sscanf(optarg, "%d", &jopt.nice_val) != 1) {
                    printf("ERROR: --nice needs an int parameter\n");
                    exit(-1);
                }
                if (jopt.nice_val < -20 || jopt.nice_val > 19) {
                    printf("ERROR: nice value has to be in -20..19 range, inclusive\n");
                    exit(-1);
                }
                break;
            case 't': case OPT_TIME_LIMIT:
                if (sscanf(optarg, "%d", &jopt.time_limit) != 1) {
                    printf("ERROR: --time-limit needs an int parameter\n");
                    exit(-1);
                }
                break;
            case 'w': case OPT_WALL_TIME_LIMIT:
                if (sscanf(optarg, "%d", &jopt.wall_time_limit) != 1) {
                    printf("ERROR: --wall-time-limit needs an int parameter\n");
                    exit(-1);
                }
                break;
            case 'm': case OPT_MEMORY_LIMIT:
                if (sscanf(optarg, "%d", &jopt.memory_limit) != 1) {
                    printf("ERROR: --memory-limit needs an int parameter\n");
                    exit(-1);
                }
                break;
            case 'v': case OPT_VERBOSE:
                jopt.verbose = 1;
                break;
            case OPT_NO_PTRACE:
                jopt.ptrace = 0;
                break;
            case OPT_COPY_LIBS:
                jopt.copy_libs = 1;
                break;
            case OPT_REDIRECT_STDIN: {
                strcpy(jopt.stdin_file, optarg);
                break;
            }
            case OPT_REDIRECT_STDOUT: {
                strcpy(jopt.stdout_file, optarg);
                break;
            }
            case OPT_REDIRECT_STDERR: {
                strcpy(jopt.stderr_file, optarg);
                break;
            }
            case OPT_BLOCK_SYSCALLS: {
                // Yes, I use strtok.
                // Yes, I know it's a horrible horrible hack.
                char* str = strdup(optarg);
                char* s;
                int id;
               
                s = strtok(str, ",");
                while (s) {
                    id = syscall_getid(s);
                    if (id < 0) {
                        printf("ERROR: Unknown system call %s\n", s);
                        exit(-1);
                    }
                    jopt.syscall_block[id] = 1;
                    s = strtok(0, ",");
                }
                break;
            }
            case OPT_BLOCK_SYSCALLS_FILE: {
                char buf[200];
                FILE* f;
                f = fopen(optarg, "rt");
                if (f == NULL) {
                    printf("Failed to open blocked syscalls file\n");
                    exit(-1);
                }
                while (fscanf(f, "%150s", buf) == 1) {
                    int id;
                    id = syscall_getid(buf);
                    if (id < 0) {
                        printf("ERROR: Unknown system call %s\n", buf);
                        exit(-1);
                    } else {
                        jopt.syscall_block[id] = 1;
                    }
                }
                fclose(f);
                break;
            }
            case OPT_CHROOT: {
                jopt.chroot = 1;
                break;
            }
            default: {
                printf("ERROR: Bad command line arguments");
                exit(-1);
            }
        }
    }

    // After options:
    
    // You need to pass the program name.
    if (jopt.prog[0] == 0) {
        printf("ERROR: You must give the file to execute\n");
        exit(-1);
    }

    // Default wall_time is time_limit + 1 second.
    if (jopt.wall_time_limit == -1 && jopt.time_limit) {
        jopt.wall_time_limit = jopt.time_limit + 1000;
    }

    // We can't follow forks. We suck.
    jopt.syscall_block[syscall_getid("fork")] = 1;
    jopt.syscall_block[syscall_getid("vfork")] = 1;
    jopt.syscall_block[syscall_getid("clone")] = 1;
    jopt.syscall_block[syscall_getid("system")] = 1;
}
