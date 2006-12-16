/*
 * Jail Run
 */

/*
 * Parameters:
 *
 *          Execution settings:
 *
 *      -p --prog: File to execute
 *      -d --dir: Directory to run in. It's assumed all dependencies are inside that dir.
 *      -v --verbose: Show debug information.
 *      --copy-libs: Try to find used libraries and copy in jail dir.
 *              Uses ldd, not very reliable. It will probably work for simple C libs,
 *              but don't expect much from it.
 *      --redirect-stdin: Redirect jailed stdin to a file (/dev/null by default).
 *              Behaves just like an empty file but avoid nasty problems.
 *      --redirect-stdout: Redirect jailed stdout to a file (/dev/null by default).
 *      --redirect-stderr: Redirect jailed stderr to a file (/dev/null by default).
 *
 *          Limits:
 *
 *      -m --memory-limit: memory limit, in kilobytes.
 *      -t --time-limit: Program "used" time limit, in miliseconds.
 *      -w --wall-time-limit: Global "wall" time limit, in miliseconds.
 *              Wall time measures actual real-world time and should be a bigger limit.
 *              This is required to avoid stalling on sleep(), etc.
 *               
 *          Security stuff:
 *
 *      -u --uid: UID for impersonation
 *      -g --gid: GID for impersonation
 *      -n --nice: Niceness factor. This can control user process priority, man nice.
 *      --chroot: To use chroot. Generally a good idea
 *      --no-ptrace: Disable ptrace and system call interception. Bad idea.
 *      --block-syscalls: Blocked system call list. Separate with ",", needs ptrace
 *      --block-syscalls-file: A file with blocked system calls. Better than the above.
 *
 
 *      Returns 0
 *      See stdout for information.
 */

#include "jrun.h"

int syscall_getid(char* name)
{
    int i;
    for (i = 0; syscall_name[i]; ++i) {
        if (!strcmp(name, syscall_name[i])) {
            return i;
        }
    }
    return -1;
}

// Copy libraries for the program in the jail dir.
void copy_libs(void)
{
    char path[200], buf[256];
    int i, k;
    FILE *p;

    sprintf(path, "ldd %s", jopt.prog);
    p = popen(path, "r");
    if (!p) {
        perror("ERROR: failed ldd");
        exit(-1);
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

// Redirect a file descriptor to file.
// if file is empty redirects to /dev/null
void redirect_fd(int fd, char* file, char* od)
{
    FILE *fp = fopen(strlen(file) ? file : "/dev/null", od);
    if (!fp) {
        perror("ERROR: Failed openning redirect file");
        exit(-1);
    }
    if (dup2(fileno(fp), fd) == -1) {
        perror("ERROR: Failed redirect (dup2)");
        exit(-1);
    }
}

void child_main(void)
{
    // child process here
    struct rlimit rl;

    // Enable ptracing.
    if (jopt.ptrace) {
        if (ptrace(PTRACE_TRACEME, 0, 0, 0) == -1) {
            perror("ERROR: failed to request tracing by the parent");
            exit(-1);
        }
    }

    // limit memory
    if (jopt.memory_limit) {
        rl.rlim_cur = rl.rlim_max = jopt.memory_limit * 1024;
        setrlimit(RLIMIT_DATA, &rl);
    }

    // limit number of processes
    rl.rlim_cur = rl.rlim_max = 50;
    setrlimit(RLIMIT_NPROC, &rl);

    //
    // FIXME: since we redirect stdin/stdout we probably lose these messages.
    // What to do.
    //

    // Redirect standard file descriptors.
    redirect_fd(0, jopt.stdin_file, "rb");
    redirect_fd(1, jopt.stdout_file, "wb");
    redirect_fd(2, jopt.stderr_file, "wb");

    // chroot to jail dir
    if (jopt.chroot) {
        if (chroot("./")) {
            perror("ERROR: Failed to chroot to jail dir");
            exit(-1);
        }
    }

    // Change user and group.
    if (jopt.gid != -1 && setgid(jopt.gid)) {
        perror("ERROR: Failed to setgid");
        exit(-1);
    }
    if (jopt.uid != -1 && setuid(jopt.uid)) {
        perror("ERROR: Failed to setuid");
        exit(-1);
    }

    // Does not return.
    if (execve(jopt.prog, 0, 0)) {
        perror("ERROR: Failed to execute program");
        exit(-1);
    }
}

// Child pid.
pid_t child_pid;

// Child stats.
int child_memory, child_time;

// If child failed for whatever reason.
// When something goes wrong this is set to 1 and message
// is set to the error message.
// The first failure message should be displayed.
int child_failed;

// Result message.
char message[500];

// Wall time. FIXME: function
int wall_time;

// Starting time.
struct timeval start_time;

// Terminate gracefully, printing memory, time and message.
// Only call after child is dead and reaped.
// Does not return.
void exit_gracefully()
{
    if (child_failed) {
        printf("FAIL: time %dms memory %dkb: %s\n",
                child_time, child_memory, message);
    } else {
        printf("OK: time %dms memory %dkb: %s\n",
                child_time, child_memory, message);
    }
    exit(0);
}

// Kills child_pid.
void fail_kill(void)
{
    if (jopt.verbose) {
        fprintf(stderr, "Trying to kill with msg %s\n", message);
    }
    if (kill(child_pid, SIGKILL)) {
        if (jopt.verbose) {
            perror("Failed kill -SIGKILL");
        }
    }
}

void ptrace_kill(void)
{
    if (jopt.verbose) {
        fprintf(stderr, "Killing with ptrace???\n");
    }
    if (ptrace(PTRACE_KILL, child_pid, 0, 0)) {
        perror("ERROR: failed ptrace kill.");
        exit(-1);
    }
}

void ptrace_detach(void)
{
    if (jopt.verbose) {
        fprintf(stderr, "Detaching ptrace before kill.\n");
    }
    if (ptrace(PTRACE_DETACH, child_pid, 0, 0)) {
        perror("ERROR: failed ptrace detach.");
        exit(-1);
    }
}

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
// it updates child_time, child_memory and wall_time.
void update_from_proc(void)
{
    char path[250];
    unsigned long int utime, stime;
    int cmem;
    struct timeval tv;
    FILE* f;
    static int last_update = -100;

    // Wall time is trivial.
    gettimeofday(&tv, 0);
    wall_time = (tv.tv_sec - start_time.tv_sec) * 1000 + (tv.tv_usec - start_time.tv_usec) / 1000;

    if (wall_time - last_update < jopt.min_proc_update_interval) {
        if (jopt.verbose) {
            fprintf(stderr, "Skipping proc update");
        }
        return;
    }
    last_update = wall_time;

    // Used time, from /proc/$pid/stat
    sprintf(path, "/proc/%d/stat", child_pid);
    if (!(f = fopen(path, "rt"))) {
        perror("ERROR: failed to read from /proc");
        exit(-1);
    }
    fscanf(f, "%*d %*s %*c %*d%*d%*d%*d%*d%*u%*u%*u%*u%*u%lu%lu", &utime, &stime);
    child_time = (utime + stime) * JRUN_JIFFIE_DURATION;
    fclose(f);

    // Memory, from /proc/$pid/stat
    sprintf(path, "/proc/%d/statm", child_pid);
    if (!(f = fopen(path, "rt"))) {
        perror("ERROR: failed to read from /proc");
        exit(-1);
    }
    // I'm not completely sure this is the right field to use.
    fscanf(f, "%*d%d%*d%*d%*d%*d", &cmem);
    cmem = (cmem * getpagesize()) / 1024;
    if (child_memory < cmem) {
        child_memory = cmem;
    }
    fclose(f);

    if (jopt.verbose) {
        //fprintf(stderr, "Running, time = %d wtime = %d mem = %d\n", used_time, wall_time, cmem);
    }
}

// gets actual time used in miliseconds from a rusage struct
void update_from_rusage(struct rusage *usage)
{
    if (jopt.verbose) {
        fprintf(stderr, "utime: %ld %ld stime: %ld %ld\n",
                usage->ru_utime.tv_sec, usage->ru_utime.tv_usec,
                usage->ru_stime.tv_sec, usage->ru_stime.tv_usec);
    }

    child_time = usage->ru_utime.tv_sec * 1000 + usage->ru_utime.tv_usec / 1000;
    child_time += usage->ru_stime.tv_sec * 1000 + usage->ru_stime.tv_usec / 1000;
}

// Does a couple of standard checks on the process.
// It sets message and child_failed if something is wrong.
// Doesn't actually kill the program.
void check_child_limits(void)
{
    if (child_failed) {
        return;
    }
    // Standard check: memory, time, wall time.
    if (jopt.memory_limit && child_memory > jopt.memory_limit) {
        strcpy(message, "Memory limit exceeded.");
        child_failed = 1;
    }
    if (jopt.time_limit && child_time > jopt.time_limit) {
        strcpy(message, "Time limit exceeded.");
        child_failed = 1;
    }
    if (jopt.time_limit && wall_time > jopt.wall_time_limit) {
        strcpy(message, "Wall time limit exceeded.");
        child_failed = 1;
    }
}

// Empty signal handler.
void empty_handler(int signal)
{
    if (jopt.verbose) {
        struct timeval tv;
        int q;
        gettimeofday(&tv, 0);
        q = (tv.tv_sec - start_time.tv_sec) * 1000 + (tv.tv_usec - start_time.tv_usec) / 1000;
        fprintf(stderr, "SIGALRM empty handler fired at %d ms\n", q);
    }
}

// Do the monkey.
void main_loop()
{
    int first_syscall = 1;

    while (1) {
        int status;
        pid_t wres;
        struct rusage usage;

        wres = wait4(child_pid, &status, 0, &usage);

        if (wres == -1 && errno == EINTR) {
            // SIGALARM, hopefully.
            if (jopt.verbose) {
                fprintf(stderr, "SIGALRM\n");
            }

            if (child_failed) {
                continue;
            }
            update_from_proc();
            check_child_limits();
            if (child_failed) {
                fail_kill();
            }
        } else if (wres == child_pid && WIFSTOPPED(status)) {
            if (child_failed) {
                if (jopt.verbose) {
                    fprintf(stderr, "Child failed but stopped in ptrace\n");
                    fprintf(stderr, "Detaching ptrace.\n");
                }
                // Child already failed, so it shouldn't stop anymore.
                ptrace_detach();
                //fail_kill();
                continue;
            }

            // Process was stopped.
            int sig, syscall_number;

            sig = WSTOPSIG(status);
            // This might cause a slowdown.
            update_from_proc();
            check_child_limits();
            if (child_failed) {
                ptrace_detach();
                fail_kill();
                continue;
            }

            if (sig != SIGTRAP) {
                if (jopt.verbose) {
                    fprintf(stderr, "Halt on stop by signal %d(%s)\n", sig, signal_name[sig]);
                }
            } else {
                // System calls, yay!
                syscall_number = get_syscall_number();
                sig = 0;

                if (jopt.verbose) {
                    fprintf(stderr, "Halt on syscall %d(%s)\n", 
                            syscall_number, syscall_name[syscall_number]);
                }
                if (jopt.syscall_block[syscall_number] && !first_syscall) {
                    child_failed = 1;
                    sprintf(message, "Blocked system call: %s.",
                            syscall_name[syscall_number]);
                    ptrace_detach();
                    fail_kill();
                    //ptrace_kill();
                    continue;
                } else {
                    if (jopt.verbose) {
                        fprintf(stderr, "Syscall %s allowed.\n", 
                                syscall_name[syscall_number]);
                    }
                }
                first_syscall = 0;
            }

            if (ptrace(PTRACE_SYSCALL, child_pid, 0, sig) == -1) {
                perror("ERROR: failed ptrace continue");
                exit(-1);
            }
        } else if (wres == child_pid && WIFEXITED(status)) {
            // Process exited normally.
            if (jopt.verbose) {
                fprintf(stderr, "Halt on process exit\n");
            }

            // I can't update_proc_status here, since the process is gone
            // reading from proc would fail.
            // Time spent is better measured with getrusage.???
            update_from_rusage(&usage);
            check_child_limits();

            // Limits are more important.
            if (child_failed == 0 && WEXITSTATUS(status) != 0) {
                child_failed = 1;
                strcpy(message, "Non-zero exit status.");
            }

            exit_gracefully();
        } else if (wres == child_pid && WIFSIGNALED(status)) {
            if (jopt.verbose) {
                fprintf(stderr, "Halt on kill by signal %d(%s).\n", 
                        WTERMSIG(status), signal_name[WTERMSIG(status)]);
            }

            // Process killed by signal.
            update_from_rusage(&usage);
            check_child_limits();
            if (child_failed == 0) {
                child_failed = 1;
                sprintf(message, "Killed by signal %d(%s).",
                        WTERMSIG(status), signal_name[WTERMSIG(status)]);
            }
            exit_gracefully();
        } else {
            perror("ERROR: waitpid failed");
            exit(-1);
        }
    }
}

int main(int argc, char *argv[], char *envp[])
{
    child_memory = 0;
    child_time = 0;
    wall_time = 0;
    child_failed = 0;
    strcpy(message, "Execution successful.");

    // Parse options
    jrun_parse_options(argc, argv);

    if (jopt.verbose) {
        setbuf(stderr, NULL);
    }

    // We do everything in --dir
    if (chdir(jopt.dir)) {
        perror("ERROR: Failed to chdir to jail dir");
        exit(-1);
    }

    // Copy libraries.
    if (jopt.copy_libs) {
        copy_libs();
    }

    // Set nice values.
    if (jopt.nice_val >= -20 && jopt.nice_val <= -19) {
        setpriority(PRIO_PROCESS, 0, jopt.nice_val);
    }

    // Get starting time. Required for tracking wall time.
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
    timer.it_interval.tv_sec = JRUN_CHECK_INTERVAL / 1000;
    timer.it_interval.tv_usec = (JRUN_CHECK_INTERVAL % 1000) * 1000;
    timer.it_value = timer.it_interval;
    if (setitimer(ITIMER_REAL, &timer, NULL) != 0) {
        perror("ERROR: setitimer failed");
        exit(-1);
    }

    main_loop();

    return 0;
}
