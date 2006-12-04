/*
 * Jail Run
 */

/*
 * Parameters.
 *
 * parameters are:
 *      -u --uid: UID for impersonation
 *      -g --gid: UID for impersonation
 *      -p --prog: File to execute
 *      -d --dir: Directory to run in. It's assumed all dependencies are inside that dir.
 *      -t --time-limit: Time limit, in miliseconds.
 *      -m --memory-limit: memory limit, in kilobytes.
 *      -n --nice: Niceness to run with. Equivalent to nice -n arg ./jrun (...)
 *      --copy-libs: Determine libs with ldd and copies them over.
 *               
 *      Returns 0
 *      See stdout
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include <getopt.h>

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
#include "tables.h"

#define CHECK_INTERVAL 50

void die(char *msg)
{
    printf("%s\n", msg);
    exit(0);
}

// Sleep in miliseconds.
void milisleep(int ms)
{
    struct timespec req;
    struct timespec rem;
    req.tv_sec = ms / 1000;
    req.tv_nsec = ms % 1000 * 1000000;

    while (nanosleep(&req, &rem)) {
        req = rem;
    }
}

// Global variables, command line options.
int opt_uid;
int opt_gid;
char opt_dir[500];
char opt_prog[500];

int opt_time_limit;
int opt_memory_limit;

int opt_nice_val;
int opt_ptrace;
int opt_copy_libs;

int opt_verbose;
int opt_chroot;

// File to redirect program stdout to.
// Can be null
char opt_stdout_file[500];

// File to redirect stderr to
// Can be null.
char opt_stderr_file[500];

// 0 or 1 if a syscall is blocked.
// Blocking means killing the process.
int syscall_block[SYSCALL_COUNT];

// Get syscall id from name.
// Returns -1 if not found.
int syscall_getid(char* name)
{
    int i;
    for (i = 0; i < SYSCALL_COUNT; ++i) {
        if (!strcmp(name, syscall_name[i])) {
            return i;
        }
    }
    return -1;
}

#define OPT_UID                         0
#define OPT_GID                         1
#define OPT_DIR                         2
#define OPT_PROG                        3
#define OPT_TIME_LIMIT                  4
#define OPT_MEMORY_LIMIT                5
#define OPT_NICE                        6
#define OPT_NO_PTRACE                   7
#define OPT_COPY_LIBS                   8
#define OPT_VERBOSE                     9
#define OPT_BLOCK_SYSCALLS              10
#define OPT_BLOCK_SYSCALLS_FILE         11
#define OPT_CAPTURE_STDOUT              12
#define OPT_CAPTURE_STDERR              13
#define OPT_CHROOT                      14

void parse_options(int argc, char *argv[])
{
    struct option long_options[] = {
        {"uid",                 1, 0, OPT_UID},
        {"gid",                 1, 0, OPT_GID},
        {"dir",                 1, 0, OPT_DIR},
        {"prog",                1, 0, OPT_PROG},
        {"time-limit",          1, 0, OPT_TIME_LIMIT},
        {"memory-limit",        1, 0, OPT_MEMORY_LIMIT},
        {"nice",                1, 0, OPT_NICE},
        {"no-ptrace",           0, 0, OPT_NO_PTRACE},
        {"copy-libs",           0, 0, OPT_COPY_LIBS},
        {"verbose",             0, 0, OPT_VERBOSE},
        {"block-syscalls",      1, 0, OPT_BLOCK_SYSCALLS},
        {"block-syscalls-file", 1, 0, OPT_BLOCK_SYSCALLS_FILE},
        {"capture-stdout",      1, 0, OPT_CAPTURE_STDOUT},
        {"capture-stderr",      1, 0, OPT_CAPTURE_STDERR},
        {"chroot",              0, 0, OPT_CHROOT},
        {0, 0, 0, 0},
    };
    int option_index = 0;

    memset(syscall_block, 0, sizeof(syscall_block));

    opt_uid = -1;
    opt_gid = -1;

    opt_prog[0] = 0;
    getcwd(opt_dir, sizeof(opt_dir));
   
    opt_time_limit = 0;
    opt_memory_limit = 0;

    opt_nice_val = -1000;
    opt_ptrace = 1;
    opt_copy_libs = 0;

    opt_verbose = 0;
    opt_chroot = 0;
    opt_stdout_file[0] = opt_stderr_file[0] = 0;

    while (1) {
        int opt = getopt_long(argc, argv, "u:g:d:p:t:m:n:v", long_options, &option_index);
        if (opt == -1) {
            break;
        }
        switch (opt) {
            case 'u': case OPT_UID:
                if (sscanf(optarg, "%d", &opt_uid) != 1) {
                    die("ERROR: --uid needs an int parameter.");
                }
                break;
            case 'g': case OPT_GID:
                if (sscanf(optarg, "%d", &opt_gid) != 1) {
                    die("ERROR: --gid needs an int parameter.");
                }
                break;
            case 'd': case OPT_DIR:
                strcpy(opt_dir, optarg);
                break;
            case 'p': case OPT_PROG:
                strcpy(opt_prog, optarg);
                break;
            case 'n': case OPT_NICE:
                if (sscanf(optarg, "%d", &opt_nice_val) != 1) {
                    die("ERROR: --nice needs an int parameter.");
                }
                if (opt_nice_val < -20 || opt_nice_val > 19) {
                    die("ERROR: nice value has to be in -20..19 range, inclusive\n");
                }
                break;
            case 't': case OPT_TIME_LIMIT:
                if (sscanf(optarg, "%d", &opt_time_limit) != 1) {
                    die("ERROR: --time-limit needs an int parameter.");
                }
                break;
            case 'm': case OPT_MEMORY_LIMIT:
                if (sscanf(optarg, "%d", &opt_memory_limit) != 1) {
                    die("ERROR: --memory-limit needs an int parameter.");
                }
                break;
            case 'v': case OPT_VERBOSE:
                opt_verbose = 1;
                break;
            case OPT_NO_PTRACE:
                opt_ptrace = 0;
                break;
            case OPT_COPY_LIBS:
                opt_copy_libs = 1;
                break;
            case OPT_CAPTURE_STDOUT: {
                strcpy(opt_stdout_file, optarg);
                break;
            }
            case OPT_CAPTURE_STDERR: {
                strcpy(opt_stderr_file, optarg);
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
                        exit(0);
                    }
                    syscall_block[id] = 1;
                    s = strtok(0, ",");
                }
                break;
            }
            case OPT_BLOCK_SYSCALLS_FILE: {
                char buf[200];
                FILE* f;
                f = fopen(optarg, "rt");
                if (f == NULL) {
                    die("Failed to open blocked syscalls file.\n");
                }
                while (fscanf(f, "%150s", buf) == 1) {
                    int id;
                    id = syscall_getid(buf);
                    if (id < 0) {
                        //fprintf(stderr, "Unknown system call %s, skipping\n", buf);
                        printf("ERROR: Unknown system call %s.\n", buf);
                        exit(0);
                    } else {
                        //fprintf(stderr, "Blocking %s\n", buf);
                        syscall_block[id] = 1;
                    }
                }
                fclose(f);
                break;
            }
            case OPT_CHROOT: {
                opt_chroot = 1;
                break;
            }
            default:
                die("ERROR: Bad command line arguments.");
        }
    }
    if (opt_prog[0] == 0) {
        die("ERROR: You must give the file to execute.");
    }
}

// Copy libraries for the program in the jail dir.
void copy_libs(void)
{
    char path[200], buf[256];
    int i, k;
    FILE *p;

    sprintf(path, "ldd %s", opt_prog);
    p = popen(path, "r");
    if (!p) {
        die("ERROR: failed ldd.\n");
    }

    path[k = 0] = 0;
    while (fgets(buf, sizeof(buf), p)) {
        for (i = 0; buf[i]; ++i) {
            if (buf[i] == ' ') {
                if (k) {
                    char cmd[512];
                    path[k] = 0;

                    // We have to create the directory first, because cp is retarded
                    sprintf(cmd, "mkdir -p ./%s", path);
                    *strrchr(cmd, '/') = 0;
                    system(cmd);
                    
                    sprintf(cmd, "cp -f %s ./%s", path, path);
                    system(cmd);
                }
                k = 0;
            } else if (buf[i] == '/' && k == 0) {
                path[k++] = buf[i];
            } else if (k != 0) {
                path[k++] = buf[i];
            }
        }
    }
    pclose(p);
}

// This should work just fine for single-cpu i386 linux
// FIXME: this is not correct on every machine
int get_jiffie_duration(void)
{
    return 10;
}

// gets actual time used in miliseconds from a rusage struct
int get_rusage_time(struct rusage *usage)
{
    int r;

    r = usage->ru_utime.tv_sec * 1000 + usage->ru_utime.tv_usec / 1000;
    r += usage->ru_stime.tv_sec * 1000 + usage->ru_stime.tv_usec / 1000;

    return r;
}

void child_main(void)
{
    // child process here
    struct rlimit rl;
    FILE* fp;

    // Enable ptracing.
    if (opt_ptrace) {
        if (ptrace(PTRACE_TRACEME, 0, 0, 0) == -1) {
            perror("ERROR: failed to request tracing by the parent");
            exit(-1);
        }
    }

    // limit memory
    if (opt_memory_limit) {
        rl.rlim_cur = rl.rlim_max = opt_memory_limit * 1024;
        setrlimit(RLIMIT_DATA, &rl);
    }

    // Change user and group.
    if (opt_gid != -1 && setgid(opt_gid)) {
        perror("ERROR: Failed to setgid");
        exit(-1);
    }
    if (opt_uid != -1 && setuid(opt_uid)) {
        perror("ERROR: Failed to setuid");
        exit(-1);
    }

    // limit number of processes
    rl.rlim_cur = rl.rlim_max = 50;
    setrlimit(RLIMIT_NPROC, &rl);

    //
    // FIXME: since we redirect stdin/stdout we probably lose these messages.
    // What to do.
    //

    // Redirect stderr.
    fp = fopen(strlen(opt_stderr_file) ? opt_stderr_file : "/dev/null", "wb");
    if (!fp) {
        perror("ERROR: Failed openning stderr redirect file");
        exit(-1);
    }
    if (dup2(fileno(fp), 2) == -1) {
        perror("ERROR: Failed stderr redirect");
        exit(-1);
    }

    // Redirect stdout.
    fp = fopen(strlen(opt_stdout_file) ? opt_stdout_file : "/dev/null", "wb");
    if (!fp) {
        perror("ERROR: Failed openning stdout redirect file");
        exit(-1);
    }
    if (dup2(fileno(fp), 1) == -1) {
        perror("ERROR: Failed stdout redirect");
        exit(-1);
    }

    // Does not return.
    if (execve(opt_prog, 0, 0)) {
        perror("ERROR: Failed to execute program");
        exit(-1);
    }

    // chroot to jail dir
    if (opt_chroot) {
        if (chroot("./")) {
            perror("ERROR: Failed to chroot to jail dir");
            exit(-1);
        }
    }
}

// Child pid.
struct timeval start_time;
int memory, used_time, wall_time;
pid_t child_pid;

// Get syscall number. MAGIC!!!
int get_syscall_number(void)
{
    int val;

    errno = 0;
    // 44, trust me. It's 4 * ORIG_EAX from ptrace.h in the kernel
    val = ptrace(PTRACE_PEEKUSER, child_pid, (char *)(44), 0);
    if (val == -1 && errno) {
        perror("ERROR: failed to get system call number");
        exit(-1);
    }

    return val;
}

// Updates child process status
// it updates used_time, wall_time and memory usage.
void update_proc_status(void)
{
    char path[250];
    unsigned long int utime, stime;
    int cmem;
    struct timeval tv;
    FILE* f;

    // Used time, from /proc/$pid/stat
    sprintf(path, "/proc/%d/stat", child_pid);
    if (!(f = fopen(path, "rt"))) {
        die("ERROR: failed to read from /proc.");
    }
    fscanf(f, "%*d %*s %*c %*d%*d%*d%*d%*d%*u%*u%*u%*u%*u%lu%lu", &utime, &stime);
    used_time = (utime + stime) * get_jiffie_duration();
    fclose(f);

    // Memory, from /proc/$pid/stat
    sprintf(path, "/proc/%d/statm", child_pid);
    if (!(f = fopen(path, "rt"))) {
        die("ERROR: failed to read from /proc.");
    }
    // I'm not completely sure this is the right field to use.
    fscanf(f, "%*d%d%*d%*d%*d%*d", &cmem);
    cmem = (cmem * getpagesize()) / 1024;
    if (memory < cmem) {
        memory = cmem;
    }
    fclose(f);

    // Wall time is trivial.
    gettimeofday(&tv, 0);
    wall_time = (tv.tv_sec - start_time.tv_sec) * 1000 + (tv.tv_usec - start_time.tv_usec) / 1000;

    if (opt_verbose) {
        fprintf(stderr, "Running, time = %d wtime = %d mem = %d\n", used_time, wall_time, cmem);
    }
}

// Report failure of the child process.
// It will also kill the child process.
void fail_kill(const char* message)
{
    struct rusage usage;

    kill(child_pid, 9);
    wait4(child_pid, 0, 0, &usage);
    used_time = get_rusage_time(&usage);
    printf("FAIL: time %dms memory %dkb: %s\n", used_time, memory, message);
    exit(0);
}

// Does a couple of standard checks on the process.
void check_proc_status(void)
{
    // Standard check: memory, time, wall time.
    if (opt_memory_limit && memory > opt_memory_limit) {
        fail_kill("Memory limit exceeded.");
    }
    if (opt_time_limit && used_time > 1.5 * opt_time_limit) {
        fail_kill("Time limit exceeded.");
    }
    if (opt_time_limit && wall_time > 5 * opt_time_limit && wall_time > 1000) {
        fail_kill("Wall time limit exceeded.");
    }
}

// Empty signal handler.
void empty_handler(int signal)
{
    struct timeval tv;
    int q;

    gettimeofday(&tv, 0);
    q = (tv.tv_sec - start_time.tv_sec) * 1000 + (tv.tv_usec - start_time.tv_usec) / 1000;

    //fprintf(stderr, "SIGALRM at %d ms\n", q);
}

int main(int argc, char *argv[], char *envp[])
{
    int first_syscall = 1;
    memory = used_time = wall_time = 0;

    // Parse options
    parse_options(argc, argv);
    syscall_block[syscall_getid("fork")] = 1;
    syscall_block[syscall_getid("vfork")] = 1;
    syscall_block[syscall_getid("clone")] = 1;
    syscall_block[syscall_getid("system")] = 1;

    // chdir
    if (chdir(opt_dir)) {
        perror("ERROR: Failed to chdir to jail dir");
        exit(-1);
    }

    // Copy libraries.
    if (opt_copy_libs) {
        copy_libs();
    }

    // Priority.
    if (opt_nice_val >= -20 && opt_nice_val <= -19) {
        setpriority(PRIO_PROCESS, 0, opt_nice_val);
    }

    gettimeofday(&start_time, 0);

    // Spawn child.
    if (!(child_pid = fork())) {
        child_main();
    }

    // Setup SIGALARM handler.
    struct sigaction sig;
    memset(&sig, 0, sizeof(sig));
    sig.sa_handler = empty_handler;
    sigaction(SIGALRM, &sig, 0);

    // Timer.
    struct itimerval timer;
    timer.it_interval.tv_sec = CHECK_INTERVAL / 1000;
    timer.it_interval.tv_usec = (CHECK_INTERVAL % 1000) * 1000;
    timer.it_value = timer.it_interval;
    if (setitimer(ITIMER_REAL, &timer, NULL) != 0) {
        perror("ERROR: setitimer failed");
        exit(-1);
    }

    while (1) {
        int status;
        pid_t wres;
        struct rusage usage;

        wres = wait4(child_pid, &status, WUNTRACED, &usage);

        if (wres == -1 && errno == EINTR) {
            // SIGALARM, hopefully.
            update_proc_status();
            check_proc_status();
        } else if (wres == child_pid) {
            if (WIFSTOPPED(status)) {
                int sig, scno;

                sig = WSTOPSIG(status);
                // This might cause a slowdown.
                update_proc_status();
                check_proc_status();

                if (sig != SIGTRAP) {
                    if (opt_verbose) {
                        fprintf(stderr, "Process stopped by signal %d(%s)\n", sig, signal_name[sig]);
                    }
                } else {
                    // System calls, yay!
                    scno = get_syscall_number();
                    if (opt_verbose) {
                        fprintf(stderr, "Process syscall %d(%s)\n", scno, syscall_name[scno]);
                    }
                    if (syscall_block[scno] && !first_syscall) {
                        char buffer[300];
                        sprintf(buffer, "Blocked system call: %s.", syscall_name[scno]);
                        fail_kill(buffer);
                    }
                    first_syscall = 0;
                    sig = 0;
                }

                if (ptrace(PTRACE_SYSCALL, child_pid, 0, sig) == -1) {
                    perror("ERROR: failed ptrace continue");
                }
            } else {
                //fprintf(stderr, "Process exitted\n");

                // Time spent is better measured with getrusage.
                used_time = get_rusage_time(&usage);
                // I can't update_proc_status here, since the process is gone
                // reading from proc would fail.
                check_proc_status();

                if (WIFEXITED(status)) {
                    if (WEXITSTATUS(status) != 0) {
                        printf("FAIL: time %dms memory %dkb: Non-zero exit status.\n", used_time, memory);
                    } else {
                        printf("OK: time %dms memory %dkb: Execution successful.\n", used_time, memory);
                    }
                } else if (WIFSIGNALED(status)) {
                    printf("FAIL: time %dms memory %dkb: Killed by signal %d(%s).\n",
                            used_time, memory, WTERMSIG(status), signal_name[WTERMSIG(status)]);
                } else {
                    printf("ERROR: Unknown reason for process termination.\n");
                }

                exit(0);
            }
        } else {
            die("ERROR: waitpid failed.\n");
        }
    }
}
