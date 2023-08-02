#!/bin/bash

export MESA_DEBUG=silent

echo ==== Resetting the testing database
php scripts/reset-testing-database.php

echo ==== Starting the Gecko driver
geckodriver&

echo ==== Running the test suite
php tests/functional-tests.php $@

echo ==== Stopping the Gecko driver
killall geckodriver
