/*
Longest common subsequence
Time complexity : O(N * M)
Memory complexity : O(N + M)
Check for details: http://www.ics.uci.edu/~eppstein/161/960229.html
To compile use: g++ -O2 -o lcs -static

The input is given in UTF-32 format because UTF-8 or UTF-16 has variable
length and you can't random acces variables. Also UTF-32 is easily converted
to int.
The output also is in UTF-32 format because of the same reasons
*/

#include <cstring>
#include <iostream>
#include <string>
#include <cstdio>
#include <fstream>
#include <vector>
using namespace std;

const int UNICODE_MARKER = 0x00FEFF;

int N, M;

vector <int> A, B, LCS;

// keeps the last 2 lines for the length of the lcs
int *len_prev;
int *len_cur;
// keeps the predecessors for the lcs
pair<int, int> *pred_prev;
pair<int, int> *pred_cur;

void lcs(int leftA, int rightA, int leftB, int rightB)
{
    if (leftA > rightA) return;
    if (leftB > rightB) return;

    int i, j, middle = (leftA + rightA) >> 1; // get the middle

    // initialize
    for (i = leftB - 1; i <= rightB; i++) {
        len_prev[i] = 0;
        len_cur[i] = 0;
        pred_prev[i] = make_pair(leftA - 1, i);
    }

    for (i = leftA; i <= rightA; i++) {
        // another initialization
        pred_cur[leftB - 1] = make_pair(i, leftB - 1);

        // DP
        for (j = leftB; j <= rightB; j++) {
            if (len_prev[j] > len_cur[j - 1]) {
                len_cur[j] = len_prev[j];
                pred_cur[j] = i - 1 <= middle ? make_pair(i - 1, j) :
                    pred_prev[j]; // update predecessor (above middle)
            } else {
                len_cur[j] = len_cur[j - 1];
                pred_cur[j] = i <= middle ? make_pair(i, j - 1) :
                    pred_cur[j - 1]; // update predecessor (above middle)
            }

            if (A[i - 1] == B[j - 1]) {
                len_cur[j] = len_prev[j - 1] + 1;
                pred_cur[j] = i - 1 <= middle ? make_pair(i - 1, j - 1) :
                    pred_prev[j - 1]; // update predecessor (above middle)
            }
        }

        // copy current line in previous line
        for (j = leftB - 1; j <= rightB; j++) {
            len_prev[j] = len_cur[j];
               pred_prev[j] = pred_cur[j];
        }
    }

    // if no lcs get out
    if (len_cur[rightB] == 0) {
        return;
    }

    if (leftA == rightA) { // if only one line, insert solution
        LCS.push_back(A[leftA - 1]);
    } else if (leftB == rightB) { // if only one column, insert solution
        LCS.push_back(B[leftB - 1]);
    } else { // solve for the two different parts (divide et impera)
        pair<int, int> p = pred_cur[rightB];

        lcs(leftA, p.first, leftB, p.second);
        lcs(p.first + 1, rightA, p.second + 1, rightB);
    }
}

int main()
{
    // read input strings

    int chr;

    // read the unicode marker
    if (!fread(&chr, 4, 1, stdin)) return 0;

    // check for UTF-32
    if (chr != UNICODE_MARKER) {
        return -1;
    }

    // read in UTF-32 (binary, 4 by 4 bytes)
    do {
        if (!fread(&chr, 4, 1, stdin)) return -1;
        A.push_back(chr);
    } while (chr != '\n');

    do {
        if (!fread(&chr, 4, 1, stdin)) return -1;
        B.push_back(chr);
    } while (chr != '\n');

    N = A.size(), M = B.size();

    // allocate memory
    len_prev = new int[M + 3];
    len_cur = new int[M + 3];
    pred_prev = new pair<int, int>[M + 3];
    pred_cur = new pair<int, int>[M + 3];

    // run lcs
    lcs(1, N, 1, M);

    // output lcs
    if (!fwrite(&UNICODE_MARKER, 4, 1, stdout)) return -1;
    for (int i = 0; i < (int) LCS.size(); i++) {
        if (!fwrite(&LCS[i], 4, 1, stdout)) return -1;
    }

    return 0;
}

