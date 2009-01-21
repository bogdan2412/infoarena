# Automated test for jrun.
# The following are parsed by test.php
# JRUN_ARGS = --time-limit 2000 --memory-limit 16000
# JRUN_RES = FAIL: time [0-9]{3,}ms memory [0-9]+kb: Time limit exceeded.

def count_sheep():
  num_sheep = 0
  while True:
    num_sheep += 1
    yield num_sheep

for num_sheeps in count_sheep():
  print "%d sheep" % num_sheeps

