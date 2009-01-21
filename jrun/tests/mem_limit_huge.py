# Automated test for jrun.
# The following are parsed by test.php
# JRUN_ARGS = --time-limit 1000 --memory-limit 10000
# JRUN_RES = FAIL: time [0-9]+ms memory [0-9]{5,}kb: Memory limit exceeded.

buffer = []
while len(buffer) < 100000:
  buffer.append("%d: %s" % (len(buffer), "SPAM" * 1000))
print "I shouldn't be here."

