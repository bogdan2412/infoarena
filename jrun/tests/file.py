# Automated test for jrun.
# The following are parsed by test.php
# JRUN_ARGS = --time-limit 1000 --memory-limit 16000
# JRUN_RES = OK: time [0-9]+ms memory [0-9]+kb: Execution successful.

open("piggybank.dat", "wt").write("\n".join(map(str, range(13))))
assert range(13) == map(int, open("piggybank.dat", "rt").readlines())

