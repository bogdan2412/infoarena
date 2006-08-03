// Automated test for jrun.
// The following are parsed by test.php
// JRUN_ARGS = --time-limit 100 --memory-limit 16000
// JRUN_RES = OK: time [0-9]+ms memory [0-9]+kb: Execution successful.
#include <iostream>
#include <fstream>
#include <vector>

using namespace std;

int main(void)
{
    vector<int> v;
    v.push_back(1);
    v.push_back(1);
    for (int i = 0; i < 1000000; ++i) {
        v.push_back(v[v.size() - 1] + v[v.size() - 2]);
    }
    cout << "Hello world";
    return 0;
}
