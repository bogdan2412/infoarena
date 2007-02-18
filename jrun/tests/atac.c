// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 3000 --memory-limit 16000
// JRUN_RES = OK: time [0-9]+ms memory [0-9]+kb: Execution successful.
//
// This is a large test that uses linked lists with malloc and lots of printf.
// This is supposed to be a performance test.
#include <stdlib.h>
#include <stdio.h>
#include <string.h>

FILE *fin, *fout;

#define MAX_LOG_N (1 << 4)
#define MAX_N (1 << MAX_LOG_N)
#define min(a, b) ((a) < (b) ? (a) : (b))

typedef struct _link_ link;

struct _link_ {
    int target, distance;
    link* next;
};

int n, m, p;
int a, b, c, d, x, y;
link* links[MAX_N];
int distance[MAX_N], position[MAX_N];
int lca_size, *lca[MAX_LOG_N];
int* prev_par[MAX_LOG_N];
int* prev_min[MAX_LOG_N];

void read(void)
{
    link* l;
    int i, par, dist;
    fscanf(fin, "%d%d%d", &n, &m, &p);
    memset(links, 0, sizeof(links));
    prev_par[0][0] = 0;
    prev_min[0][0] = 0;
    for (i = 1; i < n; ++i) {
        l = (link*)malloc(sizeof(link));
        fscanf(fin, "%d%d", &par, &dist);
        prev_par[0][i] = par - 1;
        prev_min[0][i] = dist;
        l->target = i;
        l->distance = dist;
        l->next = links[par - 1];
        links[par - 1] = l;
    }
    fscanf(fin, "%d%d%d%d%d%d", &x, &y, &a, &b, &c, &d);
/*    fprintf(fout, "%d %d  %d %d %d %d\n", x, y, a, b, c, d);*/
}

void generate(int _n, int _m)
{
    link* l;
    int i, par, dist;
    n = _n;
    p = m = _m;
    fprintf(fout, "%d %d %d\n", n, m, p);
    memset(links, 0, sizeof(links));
    prev_par[0][0] = 0;
    prev_min[0][0] = 0;
    for (i = 1; i < n; ++i) {
        l = (link*)malloc(sizeof(link));
        par = rand() % i + 1;
        dist = rand() % 100;
        fprintf(fout, "%d %d\n", par, dist);
        prev_par[0][i] = par - 1;
        prev_min[0][i] = dist;
        l->target = i;
        l->distance = dist;
        l->next = links[par - 1];
        links[par - 1] = l;
    }
    x = rand() % n + 1;
    y = rand() % n + 1;
    a = rand() % 10;
    b = rand() % 10;
    c = rand() % 10;
    d = rand() % 10;
    fprintf(fout, "%d %d  %d %d %d %d\n", x, y, a, b, c, d);

}

void df(int node)
{
    link* l;
/*    fprintf(fout, "got to %d\n", node + 1);*/
    for (l = links[node]; l; l = l->next) {
        lca[0][lca_size++] = node;
        distance[l->target] = distance[node] + 1;
        df(l->target);
    }
    position[node] = lca_size;
    lca[0][lca_size++] = node;
}

void pre_lca(void)
{
    int i, lev;
    lca_size = 0;
    df(0);
    for (lev = 1; lev < MAX_LOG_N; ++lev) {
        for (i = 0; i + (1 << lev) < MAX_N; ++i) {
            if (distance[lca[lev - 1][i]] < distance[lca[lev - 1][i + (1 << (lev - 1))]]) {
                lca[lev][i] = lca[lev - 1][i];
            } else {
                lca[lev][i] = lca[lev - 1][i + (1 << (lev - 1))];
            }
        }
    }
}

int pre_log[MAX_N];

void pre_pre_log()
{
    int i, r;
    pre_log[0] = 0;
    for (i = 1; i < MAX_N; ++i) {
        for (r = 0; (1 << r) <= i; ++r) {
            pre_log[i] = r;
        }
    }
/*    for (i = 0; i < 1000; ++i) {
        fprintf(fout, "%d ", pre_log[i]);
    }
    fprintf(fout, "\n");*/
}

int lca_get(int x, int y)
{
    int l;
    if (position[x] > position[y]) {
        return lca_get(y, x);
    }
/*    fprintf(fout, "lca(%d %d)", x + 1, y + 1);*/
    x = position[x];
    y = position[y];
    l = pre_log[y - x + 1];
/*    fprintf(fout, "%d %d %d\n", x + 1, y + 1, l);*/
    if (distance[lca[l][x]] < distance[lca[l][y - (1 << l) + 1]]) {
        return lca[l][x];
    } else {
        return lca[l][y - (1 << l) + 1];
    }
}

int dumb_lca_get(int x, int y)
{
    int i, sol;
    if (position[x] > position[y]) {
        return dumb_lca_get(y, x);
    }
    sol = x = position[x];
    y = position[y];
    for (i = x + 1; i <= y; ++i) {
        if (distance[lca[0][i]] < distance[lca[0][sol]]) {
            sol = i;
        }
    }
    return lca[0][sol];
}

void pre_proc_prev(void)
{
    int lev, i;
    for (lev = 1; lev < MAX_LOG_N; ++lev) {
        for (i = 0; i < n; ++i) {
            prev_par[lev][i] = prev_par[lev - 1][prev_par[lev - 1][i]];
            prev_min[lev][i] = min(prev_min[lev - 1][i], prev_min[lev - 1][prev_par[lev - 1][i]]);
        }
    }
}

int min_get(int x, int dist)
{
    int r, l = pre_log[dist];
    if (dist < 0) {
        exit(-1);
    }
    if (dist == 0) {
        return 0xFFFFFF;
    }
    r = min_get(prev_par[l][x], dist - (1 << l));
    /*fprintf(fout, "min_get(%d, %d, %d) = %d\n", x + 1, dist, l, min(r, prev_min[l][x]));*/
    return min(r, prev_min[l][x]);
}

int dumb_min_get(int x, int dist)
{
    int sol;
    sol = 0xFFFFFF;
    for (dist++; dist > 0; dist--)
    while (--dist) {
        sol = min(sol, prev_min[0][x]);
        x = prev_par[0][x];
    }
    return sol;
}

int get_min_break(int x, int y)
{
    int z, a1, a2;
    if (x == y) {
        return 0;
    }
    z = lca_get(x, y);
/*    if (z != dumb_lca_get(x, y)) {
        fprintf(fout, "FUCK%d %d: %d %d\n", x + 1, y + 1, z + 1, dumb_lca_get(x, y) + 1);
    }*/
/*    fprintf(fout, "get_min_break(%d %d %d)\n", x + 1, z + 1, y + 1);*/
    a1 = min_get(x, distance[x] - distance[z]);
    a2 = min_get(y, distance[y] - distance[z]);
/*    b1 = dumb_min_get(x, distance[x] - distance[z]);
    b2 = dumb_min_get(y, distance[y] - distance[z]);
    if (a1 != b1 || a2 != b2) {
        fprintf(fout, "FUCK %d != %d || %d != %d\n", a1, b1, a2, b2);
        exit(-1);
    }*/
    return min(a1, a2);
}

void solve(void)
{
    int nx, ny, z;
    pre_pre_log();
    pre_lca();
    pre_proc_prev();
/*    for (i = 0; i < lca_size; ++i) {
        fprintf(fout, "%d ", distance[lca[0][i]]);
    }
    fprintf(fout, "\n");
    for (i = 0; i < lca_size; ++i) {
        fprintf(fout, "%d ", lca[0][i] + 1);
    }
    fprintf(fout, "\n");
    for (i = 0; i < lca_size; ++i) {
        fprintf(fout, "%d ", lca[1][i] + 1);
    }
    fprintf(fout, "\n");*/
/*    for (z = 0; z < MAX_LOG_N; ++z) {
        for (i = 0; i < n; ++i) {
            fprintf(fout, "%d,%d ", prev_par[z][i] + 1, prev_min[z][i]);
        }
        fprintf(fout, "\n");
    }*/
    for (; m; m--) {
        z = get_min_break(x - 1, y - 1);
        nx = (x * a + y * b) % n + 1;
        ny = (y * c + z * d) % n + 1;
      //fprintf(fout, "%d %d = %d\n", x, y, z);
      /*  fprintf(fout, "%d = (%d * %d + %d * %d) %% %d + 1\n", nx, x, a, y, b, n);
        fprintf(fout, "%d = (%d * %d + %d * %d) %% %d + 1\n", ny, y, c, z, d, n);*/
        if (m <= p) {
            fprintf(fout, "%d\n", z);
        }
        x = nx;
        y = ny;
    }
}

void alloc()
{
    int i;
    for (i = 0; i < MAX_LOG_N; ++i) {
        prev_min[i] = (int *)malloc(MAX_N * sizeof(int));
        prev_par[i] = (int *)malloc(MAX_N * sizeof(int));
        lca[i] = (int *)malloc(MAX_N * sizeof(int));
    }
}

int main(void)
{
    fout = fopen("atac.out", "wt");

    alloc();
/*    fprintf(fout, "%d\n", sizeof(prev_min) + sizeof(prev_par) + sizeof(lca));*/
//    read();
    generate(32000, 500000);
    solve();

    fclose(fout);
    return 0;
}

