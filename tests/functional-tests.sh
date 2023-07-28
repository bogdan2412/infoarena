#!/bin/bash

export MESA_DEBUG=silent

geckodriver&

php tests/functional-tests.php

killall geckodriver
