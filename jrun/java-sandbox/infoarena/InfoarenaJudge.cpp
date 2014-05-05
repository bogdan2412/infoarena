#include <jni.h>

#include <sys/resource.h>
#include <unistd.h>

#include "../infoarena_InfoarenaJudge.h"

static int internal_setLimit(int resource, unsigned int limit) {
    struct rlimit t;
    t.rlim_cur = limit;
    t.rlim_max = limit + 1;
    if (setrlimit(resource, &t) == -1)
        return -1;
    return 0;
}

JNIEXPORT jint JNICALL Java_infoarena_InfoarenaJudge_setLimits(JNIEnv *env, jclass jcls, jint time_limit, jint file_limit, jint uid, jint gid) {
    if ((time_limit && internal_setLimit(RLIMIT_CPU, time_limit) == -1) ||
         (file_limit && internal_setLimit(RLIMIT_NOFILE, file_limit) == -1) ||
         (gid && setgid(gid) == -1) ||
         (uid && setuid(uid) == -1)) {
        return -1;
    }

    return 0;
}
