# Automated test for jrun.
# The following are parsed by test.php
# JRUN_ARGS = --time-limit 1000 --memory-limit 16000
# JRUN_RES = OK: time [0-9]+ms memory [0-9]+kb: Execution successful.

import math
from math import sin, cos, pi

n = 36
circle = [ (math.cos(i*2*pi/n), sin(i*2*pi/n)) for i in xrange(n + 1) ]
assert (circle[0][0] - circle[-1][0] < 0.0001 and \
    circle[0][1] - circle[-1][1] < 1.0000)

